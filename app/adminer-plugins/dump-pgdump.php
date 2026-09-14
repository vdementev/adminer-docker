<?php

/** Native pg_dump → zstd export (bypasses Adminer's PHP iteration)
 *
 * The PostgreSQL counterpart of `dump-mysqldump.php`: streams
 * `pg_dump … | zstd -T0` straight to the client instead of letting Adminer
 * walk the result set row by row in PHP. pg_dump is compiled C using the
 * COPY protocol, and `zstd -T0` compresses on every core, so the whole
 * pipeline stays out of PHP and finishes in a fraction of the time.
 *
 * Works for anything that speaks the PostgreSQL wire protocol and answers
 * pg_dump's catalog queries — PostgreSQL itself, and CockroachDB with the
 * usual caveats about unsupported catalog bits.
 *
 * Credentials handling:
 *   - The password goes into the child's environment as PGPASSWORD, never
 *     into argv, so `/proc/<pid>/cmdline` doesn't leak it. proc_open() gets
 *     an explicit env array (php-fpm runs with clear_env = yes, so there is
 *     nothing else in there to inherit anyway).
 *
 * Limitations:
 *   - PostgreSQL driver only.
 *   - Only the currently-selected database, and the current schema when one
 *     is selected.
 *   - `--no-owner --no-privileges`, matching what Adminer's own dump emits:
 *     the output restores as whatever role runs it.
 *   - Parallel dump (`pg_dump -j`) needs the directory format, which can't be
 *     streamed to a browser — the parallelism here is in the compressor.
 *
 * @author Vasilii Dementev
 * @license https://www.apache.org/licenses/LICENSE-2.0
 */
class AdminerDumpPgdumpZstd extends Adminer\Plugin
{
    /** Offer the mode only on a PostgreSQL connection with both binaries
     *  present. */
    function dumpOutput()
    {
        if (!function_exists('proc_open')) {
            return array();
        }
        if (!is_executable('/usr/bin/pg_dump') || !is_executable('/usr/bin/zstd')) {
            return array();
        }
        if (!defined('Adminer\\DRIVER') || \Adminer\DRIVER !== 'pgsql') {
            return array();
        }
        return array('pgzst' => 'pg_dump → zstd (fastest, native)');
    }

    /** Build the pg_dump|zstd pipeline, stream its stdout to the client,
     *  then exit before Adminer's PHP dump path runs. */
    function dumpHeaders($identifier, $multi_table = false)
    {
        if (($_POST['output'] ?? '') !== 'pgzst') {
            return null;
        }

        // ---- Resolve database + credentials ------------------------------
        $db = defined('Adminer\\DB') ? \Adminer\DB : '';
        if ($db === '') {
            // pg_dump needs a concrete database. Fall back to default flow.
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
            $port = '5432';
        }
        if ($host === '') {
            $host = 'localhost';
        }

        // ---- Tables / schema ---------------------------------------------
        // Adminer collects checked tables in both tables[] and data[]. Names
        // are restricted to plain identifiers: pg_dump treats -t as a pattern,
        // where dots separate schema from table and `*?` are wildcards.
        $tables = array_unique(array_merge(
            (array)($_POST['tables'] ?? array()),
            (array)($_POST['data'] ?? array())
        ));
        $plainIdentifier = function ($name) {
            return is_string($name) && preg_match('~^[A-Za-z0-9_$]+$~', $name);
        };
        $tables = array_filter($tables, $plainIdentifier);

        // Adminer keeps the selected schema in ?ns= — dump just that one, the
        // same slice the user is looking at.
        $schema = (string)($_GET['ns'] ?? '');
        if ($schema !== '' && !$plainIdentifier($schema)) {
            $schema = '';
        }

        // ---- pg_dump flags -----------------------------------------------
        $flags = array(
            '--host=' . escapeshellarg($host),
            '--port=' . escapeshellarg($port),
            '--username=' . escapeshellarg($user),
            '--no-password',                    // never prompt; PGPASSWORD or bust
            '--format=plain',                   // streamable SQL text
            '--no-owner',                       // restorable as any role
            '--no-privileges',                  // ditto for GRANTs
            '--quote-all-identifiers',          // survives reserved-word column names
        );
        if (($_POST['table_style'] ?? '') === 'DROP+CREATE') {
            $flags[] = '--clean';
            $flags[] = '--if-exists';
        }
        // Structure-only: user unchecked all data[] entries.
        if (empty($_POST['data']) && !empty($_POST['tables'])) {
            $flags[] = '--schema-only';
        }
        // Data-only: user unchecked all tables[] entries, kept data[].
        if (!empty($_POST['data']) && empty($_POST['tables'])) {
            $flags[] = '--data-only';
        }
        if ($schema !== '') {
            $flags[] = '--schema=' . escapeshellarg($schema);
        }
        foreach ($tables as $t) {
            $flags[] = '--table=' . escapeshellarg(($schema !== '' ? $schema . '.' : '') . $t);
        }

        $cmd = '/usr/bin/pg_dump ' . implode(' ', $flags) . ' ' . escapeshellarg($db)
             . ' </dev/null | /usr/bin/zstd -c -3 -T0 --no-progress';

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
        $env = array(
            'PGPASSWORD' => (string)$password,
            'PGCONNECT_TIMEOUT' => '30',
            'PATH' => '/usr/local/bin:/usr/bin:/bin',
            'LANG' => 'C.UTF-8',
        );
        $proc = proc_open($cmd, $descriptors, $pipes, null, $env);
        if (!is_resource($proc)) {
            return null; // couldn't spawn — fall back to Adminer's default flow
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

        if ($rc !== 0 || $stderrBuf !== '') {
            error_log('[pg_dump→zstd] exit=' . $rc . ' stderr=' . trim(substr($stderrBuf, 0, 500)));
        }

        exit; // hard stop: don't let Adminer's row-iteration flow run.
    }

    protected $translations = array(
        'cs' => array('pg_dump → zstd (fastest, native)' => 'pg_dump → zstd (nejrychlejší, nativní)'),
        'de' => array('pg_dump → zstd (fastest, native)' => 'pg_dump → zstd (am schnellsten, nativ)'),
        'fr' => array('pg_dump → zstd (fastest, native)' => 'pg_dump → zstd (le plus rapide, natif)'),
        'es' => array('pg_dump → zstd (fastest, native)' => 'pg_dump → zstd (el más rápido, nativo)'),
        'ru' => array('pg_dump → zstd (fastest, native)' => 'pg_dump → zstd (самый быстрый, нативный)'),
        'ja' => array('pg_dump → zstd (fastest, native)' => 'pg_dump → zstd（最速、ネイティブ）'),
    );
}
