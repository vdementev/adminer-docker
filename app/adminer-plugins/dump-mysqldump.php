<?php

/** Native mysqldump → zstd export (bypasses Adminer's PHP iteration)
 *
 * Streams `mysqldump … | zstd -T0` directly to the client. Roughly an order
 * of magnitude faster than Adminer's row-by-row PHP dump path: mysqldump is
 * compiled C reading rows through the libmysql buffered/unbuffered API, and
 * `zstd -T0` uses every core for compression.
 *
 * The plugin short-circuits Adminer's normal dump function by calling `exit`
 * from `dumpHeaders()` once streaming is done. Adminer's row-iteration loop
 * never runs for this output mode.
 *
 * Credentials handling:
 *   - Adminer's `credentials()` returns [server, user, password].
 *   - We write them into a 0600 temp file and pass to mysqldump via
 *     `--defaults-extra-file=…`. Argv stays free of the password, so
 *     `/proc/<pid>/cmdline` doesn't leak it.
 *   - Temp file is unlinked on completion (and on error via shutdown).
 *
 * Limitations:
 *   - MySQL/MariaDB only (Adminer driver "server").
 *   - Only the currently-selected database is dumped (mysqldump invocation).
 *   - Per-table "data vs structure" split is not honored — mysqldump runs
 *     once and produces both. To dump structure only, leave `data_style`
 *     empty; to dump data only, leave `table_style` empty.
 *
 * @author Vasilii Dementev
 * @license https://www.apache.org/licenses/LICENSE-2.0
 */
class AdminerDumpMysqldumpZstd extends Adminer\Plugin
{
    /** @var string|null absolute path to the temp defaults-extra-file */
    private $tmpCnf = null;

    /** Show "mysqldump → zstd" only when binaries are present and we're on
     *  a MySQL/MariaDB connection. */
    function dumpOutput()
    {
        if (!function_exists('proc_open')) {
            return array();
        }
        if (!is_executable('/usr/bin/mysqldump') || !is_executable('/usr/bin/zstd')) {
            return array();
        }
        // Adminer driver constant — only the "server" (MySQL) driver supports
        // mysqldump. We don't gate on it strictly to avoid namespace-resolution
        // brittleness; mysqldump will fail loudly on non-MySQL backends.
        return array('mzst' => 'mysqldump → zstd (fastest, native)');
    }

    /** Build the mysqldump|zstd pipeline, stream its stdout to the client,
     *  then exit before Adminer's PHP dump path runs. */
    function dumpHeaders($identifier, $multi_table = false)
    {
        if (($_POST['output'] ?? '') !== 'mzst') {
            return null;
        }

        // ---- Resolve database + credentials ------------------------------
        $db = defined('Adminer\\DB') ? \Adminer\DB : '';
        if ($db === '') {
            // mysqldump needs a concrete database. Fall back to default flow.
            return null;
        }

        $creds = \Adminer\adminer()->credentials();
        if (!is_array($creds) || count($creds) < 3) {
            return null;
        }
        list($server, $user, $password) = $creds;

        if (strpos($server, ':') !== false) {
            list($host, $port) = explode(':', $server, 2);
        } else {
            $host = $server;
            $port = '3306';
        }

        // ---- Tables list -------------------------------------------------
        // Adminer collects checked tables in both tables[] and data[].
        // mysqldump dumps both structure and data for any table named in argv
        // (with --add-drop-table for DROP+CREATE).
        $tables = array_unique(array_merge(
            (array)($_POST['tables'] ?? array()),
            (array)($_POST['data'] ?? array())
        ));
        // Reject anything with shell-meaningful characters as a safety net
        // on top of escapeshellarg() below.
        $tables = array_filter($tables, function ($t) {
            return is_string($t) && preg_match('~^[A-Za-z0-9_$]+$~', $t);
        });

        // ---- defaults-extra-file with credentials ------------------------
        $this->tmpCnf = tempnam(sys_get_temp_dir(), 'adminer-my-');
        if ($this->tmpCnf === false) {
            return null;
        }
        chmod($this->tmpCnf, 0600);
        // Wrap password in double quotes and escape \ and " per my.cnf rules.
        $escapedPw = '"' . addcslashes($password, "\\\"") . '"';
        file_put_contents(
            $this->tmpCnf,
            "[client]\n"
            . "host = " . $host . "\n"
            . "port = " . $port . "\n"
            . "user = " . $user . "\n"
            . "password = " . $escapedPw . "\n"
        );
        // Ensure cleanup even if a fatal error happens mid-stream.
        $tmp = $this->tmpCnf;
        register_shutdown_function(function () use ($tmp) {
            if (file_exists($tmp)) {
                @unlink($tmp);
            }
        });

        // ---- mysqldump flags ---------------------------------------------
        // NOTE: image ships MariaDB's mysqldump (`default-mysql-client` on Debian
        // trixie = mariadb-client). It connects fine to MySQL 8.4, but rejects
        // MySQL-only flags like `--set-gtid-purged=OFF`. Keep this list to flags
        // supported by both.
        $flags = array(
            '--defaults-extra-file=' . escapeshellarg($this->tmpCnf),
            '--quick',                          // stream rows, don't buffer in client
            '--single-transaction',             // InnoDB consistent snapshot, no locks
            '--extended-insert',                // multi-row INSERTs (smaller + faster)
            '--complete-insert',                // column list in INSERT (resilient)
            '--no-tablespaces',                 // skip tablespace info (no PROCESS priv needed)
            '--skip-dump-date',                 // omit volatile comment (better cache)
            '--hex-blob',                       // binary-safe blob columns
            '--ssl-verify-server-cert=0',       // keep TLS encryption but accept the self-signed server cert
        );
        if (($_POST['table_style'] ?? '') === 'DROP+CREATE') {
            $flags[] = '--add-drop-table';
        }
        if (empty($_POST['triggers'])) {
            $flags[] = '--skip-triggers';
        }
        if (!empty($_POST['routines'])) {
            $flags[] = '--routines';
        }
        if (!empty($_POST['events'])) {
            $flags[] = '--events';
        }
        // Structure-only: user unchecked all data[] entries.
        if (empty($_POST['data']) && !empty($_POST['tables'])) {
            $flags[] = '--no-data';
        }
        // Data-only: user unchecked all tables[] entries, kept data[].
        if (!empty($_POST['data']) && empty($_POST['tables'])) {
            $flags[] = '--no-create-info';
        }

        $cmd = '/usr/bin/mysqldump ' . implode(' ', $flags)
             . ' ' . escapeshellarg($db);
        if ($tables) {
            foreach ($tables as $t) {
                $cmd .= ' ' . escapeshellarg($t);
            }
        }
        $cmd .= ' </dev/null | /usr/bin/zstd -c -3 -T0 --no-progress';

        // ---- Output headers ----------------------------------------------
        $base = \Adminer\adminer()->dumpFilename($identifier);
        $filename = $base . '.sql.zst';
        header('Content-Type: application/zstd');
        header('Content-Disposition: attachment; filename="' . addslashes($filename) . '"');

        // Adminer hasn't started an OB at this point (its `if (!ob_get_level())
        // ob_start(...)` runs AFTER dumpHeaders returns). We exit before that,
        // so we can safely echo directly to the FastCGI sink.
        session_write_close();

        // ---- Spawn pipeline & stream -------------------------------------
        $descriptors = array(
            0 => array('pipe', 'r'),   // empty stdin (closed immediately)
            1 => array('pipe', 'w'),   // zstd stdout → us
            2 => array('pipe', 'w'),   // shell stderr (drained continuously)
        );
        $proc = proc_open($cmd, $descriptors, $pipes);
        if (!is_resource($proc)) {
            // Couldn't even spawn — cleanup and fall back to Adminer default.
            @unlink($this->tmpCnf);
            return null;
        }
        fclose($pipes[0]);
        stream_set_blocking($pipes[1], false);
        stream_set_blocking($pipes[2], false);

        $stderrBuf = '';
        while (true) {
            $r = array($pipes[1], $pipes[2]);
            $w = null;
            $x = null;
            $ready = @stream_select($r, $w, $x, 5);
            if ($ready === false) {
                // Interrupted (signal) — retry. Avoid spinning forever:
                $status = proc_get_status($proc);
                if (!$status['running']) break;
                continue;
            }
            if ($ready === 0) {
                // 5s with nothing — check if the child is still alive.
                $status = proc_get_status($proc);
                if (!$status['running']) break;
                continue;
            }
            foreach ($r as $pipe) {
                $chunk = fread($pipe, 65536);
                if ($chunk === '' || $chunk === false) continue;
                if ($pipe === $pipes[1]) {
                    echo $chunk;
                    flush();
                } else {
                    $stderrBuf .= $chunk;
                }
            }
            $status = proc_get_status($proc);
            if (!$status['running'] && feof($pipes[1])) {
                break;
            }
        }

        // Drain any final bytes after child exit.
        stream_set_blocking($pipes[1], true);
        while (!feof($pipes[1])) {
            $chunk = fread($pipes[1], 65536);
            if ($chunk === '' || $chunk === false) break;
            echo $chunk;
            flush();
        }
        $stderrBuf .= stream_get_contents($pipes[2]) ?: '';
        fclose($pipes[1]);
        fclose($pipes[2]);
        $rc = proc_close($proc);

        @unlink($this->tmpCnf);

        if ($rc !== 0 || $stderrBuf !== '') {
            error_log('[mysqldump→zstd] exit=' . $rc . ' stderr=' . trim(substr($stderrBuf, 0, 500)));
        }

        exit; // hard stop: don't let Adminer's row-iteration flow run.
    }

    protected $translations = array(
        'cs' => array('mysqldump → zstd (fastest, native)' => 'mysqldump → zstd (nejrychlejší, nativní)'),
        'de' => array('mysqldump → zstd (fastest, native)' => 'mysqldump → zstd (am schnellsten, nativ)'),
        'fr' => array('mysqldump → zstd (fastest, native)' => 'mysqldump → zstd (le plus rapide, natif)'),
        'es' => array('mysqldump → zstd (fastest, native)' => 'mysqldump → zstd (el más rápido, nativo)'),
        'ru' => array('mysqldump → zstd (fastest, native)' => 'mysqldump → zstd (самый быстрый, нативный)'),
        'ja' => array('mysqldump → zstd (fastest, native)' => 'mysqldump → zstd（最速、ネイティブ）'),
    );
}
