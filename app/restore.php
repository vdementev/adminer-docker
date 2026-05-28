<?php

/**
 * Fast Database Restore — companion page to Adminer
 *
 * Receives an uploaded SQL dump (.sql, .sql.gz, or .sql.zst), then shells out
 * to native CLI:  decompressor | mysql --defaults-extra-file=…
 *
 * Pairs with the `dump-mysqldump.php` Adminer plugin: that one produces the
 * .sql.zst, this one ingests it. End-to-end the path stays in C-land
 * (mysqldump / zstd / mysql), bypassing Adminer's row-by-row PHP iteration
 * for ~order-of-magnitude speedup vs the built-in Adminer import.
 *
 * Workflow guarantee — "upload first, then restore":
 *   PHP's multipart handler buffers the entire POST body to `upload_tmp_dir`
 *   BEFORE this script is invoked. By the time the script runs, the upload
 *   is complete on disk (regardless of how slow the client side was), so a
 *   subsequent client-network drop can't kill the import. We then move the
 *   tmp file to a stable path and stream it into mysql.
 *
 * Auth — relies on the user having an active Adminer session. Sessions are
 *   the same PHP session as Adminer because we session_start() the same way
 *   and the FastCGI script lives in the same PHP-FPM pool. If $_SESSION has
 *   no Adminer pwds entries, we bounce to Adminer's login page.
 *
 * Routed at /adminer/restore.php via an Angie `location = …` override in
 * angie-base.conf — the existing prefix `location /adminer/` hardcodes
 * SCRIPT_FILENAME=/app/index.php, so we need an exact-match override that
 * remaps to /app/restore.php.
 *
 * @author Vasilii Dementev
 * @license https://www.apache.org/licenses/LICENSE-2.0
 */

declare(strict_types=1);

// Adminer's session uses the `adminer_sid` cookie name on cookie path
// `/adminer/`. To read Adminer's $_SESSION we have to use the same name +
// path BEFORE session_start(); otherwise PHP issues a fresh PHPSESSID
// session under `/` and we see nothing.
session_name('adminer_sid');
session_set_cookie_params([
    'path'     => '/adminer/',
    'secure'   => true,
    'httponly' => true,
    'samesite' => 'Lax',
]);
session_start();

// Auth gate: require the user to have an active Adminer cookie. The MySQL
// credentials posted to this page are validated by mysql/mysqldump on
// their own (wrong → fail loudly), so this just stops random scanners
// from probing the form.
if (empty($_COOKIE['adminer_sid'])) {
    header('Location: ./');
    exit;
}

function h(string $s): string
{
    return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
}

function fmtBytes(int $n): string
{
    if ($n < 1024) return $n . ' B';
    if ($n < 1024 * 1024) return number_format($n / 1024, 1) . ' KiB';
    if ($n < 1024 * 1024 * 1024) return number_format($n / 1024 / 1024, 1) . ' MiB';
    return number_format($n / 1024 / 1024 / 1024, 2) . ' GiB';
}

$msg = '';
$ok  = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_FILES['dump']['tmp_name'])) {
    $host = trim((string)($_POST['host']     ?? ''));
    $port = (int)         ($_POST['port']     ?? 3306);
    $user = trim((string)($_POST['user']     ?? ''));
    $pass =      (string)($_POST['password'] ?? '');
    $db   = trim((string)($_POST['db']       ?? ''));

    if ($host === '' || $user === '' || $db === '') {
        $msg = 'Host, user, and database are required.';
    } elseif ($_FILES['dump']['error'] !== UPLOAD_ERR_OK) {
        $msg = sprintf('Upload error (code %d).', $_FILES['dump']['error']);
    } else {
        // ---- Stage the upload to a path mysqldump can read ---------------
        $staged = '/tmp/restore-' . bin2hex(random_bytes(8));
        if (!move_uploaded_file($_FILES['dump']['tmp_name'], $staged)) {
            $msg = 'Could not stage upload (move_uploaded_file failed).';
        } else {
            chmod($staged, 0644);
            $stagedSize = (int)filesize($staged);

            // ---- Detect compression by magic bytes ----------------------
            $fh = fopen($staged, 'rb');
            $magic = (string)fread($fh, 4);
            fclose($fh);

            if (strncmp($magic, "\x28\xb5\x2f\xfd", 4) === 0) {
                $decomp = '/usr/bin/zstd -dc';
                $fmt    = 'zstd';
            } elseif (strncmp($magic, "\x1f\x8b", 2) === 0) {
                $decomp = '/usr/bin/gunzip -c';
                $fmt    = 'gzip';
            } else {
                $decomp = '/bin/cat';
                $fmt    = 'plain SQL';
            }

            // ---- Defaults file (keeps password out of argv) ------------
            $cnf = (string)tempnam('/tmp', 'restore-cnf-');
            chmod($cnf, 0600);
            $pwEsc = '"' . addcslashes($pass, "\\\"") . '"';
            file_put_contents($cnf, sprintf(
                "[client]\nhost = %s\nport = %d\nuser = %s\npassword = %s\n",
                $host, $port, $user, $pwEsc
            ));
            // Be sure both temp files are cleaned up even on fatal errors.
            register_shutdown_function(function () use ($cnf, $staged) {
                @unlink($cnf);
                @unlink($staged);
            });

            // ---- Build & run pipeline -----------------------------------
            $cmd = sprintf(
                '%s < %s | /usr/bin/mysql --defaults-extra-file=%s --ssl-verify-server-cert=0 --default-character-set=utf8mb4 %s 2>&1',
                $decomp,
                escapeshellarg($staged),
                escapeshellarg($cnf),
                escapeshellarg($db)
            );

            $startedAt = microtime(true);

            $proc = proc_open(
                $cmd,
                [
                    0 => ['pipe', 'r'],
                    1 => ['pipe', 'w'],
                    2 => ['pipe', 'w'],
                ],
                $pipes
            );

            if (!is_resource($proc)) {
                $msg = 'proc_open failed — could not start restore pipeline.';
            } else {
                fclose($pipes[0]);
                $stdout = (string)stream_get_contents($pipes[1]);
                $stderr = (string)stream_get_contents($pipes[2]);
                fclose($pipes[1]);
                fclose($pipes[2]);
                $rc = proc_close($proc);

                $elapsed = microtime(true) - $startedAt;

                if ($rc === 0) {
                    $ok  = true;
                    $msg = sprintf(
                        "Restore completed in %.2f s.\n"
                        . "  file:   %s (%s, %s)\n"
                        . "  target: %s on %s:%d as %s\n",
                        $elapsed,
                        $_FILES['dump']['name'],
                        fmtBytes($stagedSize),
                        $fmt,
                        $db, $host, $port, $user
                    );
                    if (trim($stdout) !== '') {
                        $msg .= "\n--- mysql stdout ---\n" . $stdout;
                    }
                    if (trim($stderr) !== '') {
                        $msg .= "\n--- mysql stderr (warnings) ---\n" . $stderr;
                    }
                } else {
                    $msg = sprintf(
                        "Restore FAILED (exit code %d, %.2f s).\n"
                        . "  file: %s (%s, %s)\n\n"
                        . "--- mysql stdout ---\n%s\n--- mysql stderr ---\n%s",
                        $rc, $elapsed,
                        $_FILES['dump']['name'],
                        fmtBytes($stagedSize),
                        $fmt,
                        $stdout, $stderr
                    );
                }
            }
        }
    }
}

// ---- Sensible defaults for the form (from previous POST or query) -------
$prefill = function (string $key, string $fallback = '') {
    return (string)($_POST[$key] ?? $_GET[$key] ?? $fallback);
};

?><!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Fast Restore — Adminer</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<style>
  :root { color-scheme: light dark; }
  body { font-family: -apple-system, Segoe UI, Roboto, sans-serif; max-width: 760px; margin: 2em auto; padding: 0 1em; }
  h1 { margin: 0; }
  .desc { color: #666; margin: 0.5em 0 1.5em; }
  fieldset { border: 1px solid #ccc; padding: 1em 1.5em; margin: 1em 0; border-radius: 6px; }
  legend { padding: 0 0.5em; font-weight: bold; }
  label { display: block; margin: 0.7em 0 0.2em; font-weight: 500; }
  input[type=text], input[type=password], input[type=number], input[type=file] {
    width: 100%; padding: 0.45em; box-sizing: border-box;
    border: 1px solid #aaa; border-radius: 4px; font: inherit;
  }
  input[type=file] { padding: 0.4em 0; border: none; }
  button { padding: 0.6em 1.4em; font-size: 1em; cursor: pointer; border: 0; border-radius: 4px; background: #2563eb; color: #fff; }
  button:hover { background: #1d4ed8; }
  .msg { white-space: pre-wrap; font-family: ui-monospace, Consolas, monospace; padding: 1em; border-radius: 6px; margin: 1em 0; font-size: 0.9em; line-height: 1.4; }
  .msg.ok    { background: #ecfdf5; border: 1px solid #10b981; color: #064e3b; }
  .msg.err   { background: #fef2f2; border: 1px solid #ef4444; color: #7f1d1d; }
  .limits { color: #888; font-size: 0.85em; margin-top: 0.3em; }
  .back { float: right; }
  .row { display: flex; gap: 1em; }
  .row > div { flex: 1; }
</style>
</head>
<body>

<a class="back" href="./">← Adminer</a>
<h1>Fast Database Restore</h1>
<p class="desc">
  Pipes the uploaded SQL dump through the native <code>mysql</code> client.
  Bypasses Adminer's PHP row iteration — roughly an order of magnitude faster on multi-GB dumps.
  Detected formats: <code>.sql</code>, <code>.sql.gz</code>, <code>.sql.zst</code>.
</p>

<?php if ($msg): ?>
  <div class="msg <?= $ok ? 'ok' : 'err' ?>"><?= h($msg) ?></div>
<?php endif; ?>

<form method="post" enctype="multipart/form-data">
  <fieldset>
    <legend>Target MySQL</legend>
    <div class="row">
      <div>
        <label>Host</label>
        <input type="text" name="host" required value="<?= h($prefill('host', 'mysql-primary')) ?>">
      </div>
      <div>
        <label>Port</label>
        <input type="number" name="port" required value="<?= h($prefill('port', '3306')) ?>">
      </div>
    </div>
    <label>User</label>
    <input type="text" name="user" required value="<?= h($prefill('user', 'root')) ?>">
    <label>Password</label>
    <input type="password" name="password" autocomplete="off">
    <label>Database (must already exist)</label>
    <input type="text" name="db" required value="<?= h($prefill('db')) ?>">
  </fieldset>

  <fieldset>
    <legend>SQL dump file</legend>
    <input type="file" name="dump" required>
    <div class="limits">
      Max upload: <code><?= h((string)ini_get('upload_max_filesize')) ?></code> /
      max POST: <code><?= h((string)ini_get('post_max_size')) ?></code>.
      The file is staged to <code>/tmp</code> on the server before mysql ingests it.
    </div>
  </fieldset>

  <p><button type="submit">Restore</button></p>
</form>

</body>
</html>
