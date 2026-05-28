<?php

/** Streaming parallel-compression Adminer plugins
 *
 * This file ships TWO Adminer plugins that pipe Adminer's dump output through
 * an external compressor via `proc_open` so that all CPU cores can be used:
 *
 *   - AdminerDumpPigz   →  output value `pigz`  →  *.sql.gz  (pigz -c -6)
 *   - AdminerDumpPzst   →  output value `pzst`  →  *.sql.zst (zstd -c -3 -T0)
 *
 * Why proc_open instead of `gzencode()` / `zstd_compress_add()`:
 *   PHP's built-in zlib/zstd extensions are single-threaded. On the 16-core
 *   prod host the dump pipeline becomes CPU-bound on one core. Shelling out
 *   to pigz/zstd CLI uses every core and finishes in a fraction of the time,
 *   shrinking the window in which an HTTP/2 flow-control stall, a Cloudflare
 *   idle timeout, or a MySQL net_write_timeout can kill the connection.
 *
 * Why proc_open and not exec/popen:
 *   - The image's php.ini deliberately disables exec/system/shell_exec/passthru.
 *   - `proc_open` is enabled and accepts an argv array (no shell parsing →
 *     no injection vector).
 *
 * Why a trait instead of an abstract base class:
 *   Adminer's `Plugins` bootstrap auto-instantiates every declared subclass
 *   of `Adminer\Plugin` (`Cannot instantiate abstract class …` otherwise).
 *   A trait stays invisible to that scan.
 *
 * Adminer plugin gotchas (already burned by these on the zstd plugin):
 *   - `dumpHeaders()` MUST return a NON-EMPTY STRING (the underlying format
 *     identifier, e.g. "sql"). Returning bool/null trips Adminer's loose
 *     equality `$Mc == "tar"` and enters tar-archive mode → garbage output.
 *   - Adminer's outer wrapper sets `Content-Disposition` itself, building
 *     filename as `<dumpFilename>.<format>.<output>` — so the radio value
 *     ("pigz" / "pzst") doubles as the file extension. We use "pigz" rather
 *     than "gz" so we don't collide with Adminer's built-in `output=gz`.
 *
 * @author Vasilii Dementev
 * @license https://www.apache.org/licenses/LICENSE-2.0
 */


/** Shared streaming-through-subprocess implementation. Concrete plugins
 *  define key()/label()/argv()/contentType()/isAvailable() and `use` this. */
trait AdminerProcOpenCompressTrait
{
    /** @var resource|null proc_open() process handle */
    private $proc = null;
    /** @var resource|null pipe to write SQL into */
    private $stdin = null;
    /** @var resource|null pipe to read compressed bytes from */
    private $stdout = null;
    /** @var resource|null stderr (drained every chunk to avoid a 64 KiB blockage) */
    private $stderr = null;
    /** @var string accumulated stderr — emitted to error_log at FINAL */
    private $stderrBuf = '';

    function dumpOutput()
    {
        if (!function_exists('proc_open') || !$this->isAvailable()) {
            return array();
        }
        return array($this->key() => $this->label());
    }

    function dumpHeaders($identifier, $multi_table = false)
    {
        if (($_POST['output'] ?? '') !== $this->key()) {
            return null;
        }

        $format = preg_match('~sql~', $_POST['format'] ?? '')
            ? 'sql'
            : ($multi_table ? 'tar' : 'csv');

        // All three streams as pipes. We drain stderr every chunk — leaving it
        // unread would let any stray compressor warning fill the kernel pipe
        // (~64 KiB) and the child would block on stderr → truncated dump
        // hanging mid-stream. (We can't redirect to /dev/null because
        // open_basedir doesn't include /dev.)
        $descriptors = array(
            0 => array('pipe', 'r'),
            1 => array('pipe', 'w'),
            2 => array('pipe', 'w'),
        );

        $this->proc = proc_open($this->argv(), $descriptors, $pipes);
        if (!is_resource($this->proc)) {
            return null;
        }

        $this->stdin  = $pipes[0];
        $this->stdout = $pipes[1];
        $this->stderr = $pipes[2];

        // Non-blocking on the read side so we can drain mid-stream without
        // hanging when the compressor hasn't produced output yet (pigz holds
        // a 128 KiB block, zstd holds its window). Stderr too — drained
        // alongside stdout so it can't fill and deadlock the child.
        stream_set_blocking($this->stdout, false);
        stream_set_blocking($this->stderr, false);

        self::tuneDumpSession();

        header('Content-Type: ' . $this->contentType());

        // Bounded buffer — each callback gets at most ~64 KiB of raw SQL.
        ob_start(array($this, 'pipeChunk'), 65536);

        return $format;
    }

    /**
     * Issue per-session MySQL pragmas that skip work we don't need during a
     * dump. All are best-effort — some require SYSTEM_VARIABLES_ADMIN /
     * SESSION_VARIABLES_ADMIN; failures are silently ignored so a low-privilege
     * Adminer user still gets a working dump (just without the speedup).
     */
    private static function tuneDumpSession(): void
    {
        $conn = Adminer\connection();
        if (!$conn) {
            return;
        }
        // Skip the slow-query-log evaluation path entirely for this connection.
        // Saves the per-query "is this slow?" branch + the 1-in-10 file write.
        @$conn->query('SET SESSION slow_query_log = 0');
        @$conn->query('SET SESSION long_query_time = 31536000');  // ≈ 1 year — no query qualifies as slow
        @$conn->query('SET SESSION sql_log_off = 1');             // skip the general log too (no-op if off)

        // Disable binlog for this session. SELECTs aren't written to binlog
        // anyway, but Adminer issues SET / temp-table commands that are; this
        // shaves the binlog-row-image computation off them.
        @$conn->query('SET SESSION sql_log_bin = 0');

        // Long network timeouts in case the dump streams for an hour over a
        // slow link — independent of primary.cnf's global net_*_timeout.
        @$conn->query('SET SESSION net_read_timeout = 14400');
        @$conn->query('SET SESSION net_write_timeout = 14400');

        // Use the same consistent-snapshot trick mysqldump --single-transaction
        // uses: for InnoDB this reads a stable point-in-time view without
        // taking table locks. No-op for non-InnoDB tables.
        @$conn->query('SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ');
        @$conn->query('START TRANSACTION WITH CONSISTENT SNAPSHOT');
    }

    /** ob_start callback. Returns whatever compressed bytes are ready. */
    function pipeChunk($string, $state)
    {
        if ($this->proc === null) {
            return $string;
        }

        $out = '';

        // 1. Write input. Loop on partial writes — the kernel pipe is ~64 KiB
        //    so a full ob chunk may not fit in one go.
        $offset = 0;
        $len = strlen($string);
        while ($offset < $len) {
            $n = fwrite($this->stdin, substr($string, $offset));
            if ($n === false || $n === 0) {
                // stdin full or compressor gone. Drain stdout to make room,
                // then retry — if even that drains nothing, give up; FINAL
                // will surface stderr.
                $drained = $this->drain();
                if ($drained === '') {
                    break;
                }
                $out .= $drained;
                continue;
            }
            $offset += $n;
        }

        // 2. Drain stdout (compressed data → client) AND stderr (discarded,
        //    but must be read to prevent the child blocking on writes to it).
        $out .= $this->drain();
        $this->drainStderr();

        if ($state & PHP_OUTPUT_HANDLER_FINAL) {
            // 3. Signal EOF and let the compressor flush its last block(s).
            fclose($this->stdin);
            $this->stdin = null;

            // Switch to blocking for the tail: bounded by how long the
            // compressor takes to finish remaining work — milliseconds.
            stream_set_blocking($this->stdout, true);
            while (!feof($this->stdout)) {
                $chunk = fread($this->stdout, 65536);
                if ($chunk === false || $chunk === '') break;
                $out .= $chunk;
            }
            fclose($this->stdout);
            $this->stdout = null;

            // Final stderr drain + close.
            $this->drainStderr();
            fclose($this->stderr);
            $this->stderr = null;

            $rc = proc_close($this->proc);
            $this->proc = null;
            if ($rc !== 0 || $this->stderrBuf !== '') {
                error_log('[AdminerProcOpenCompress ' . $this->key()
                    . '] exit=' . $rc
                    . ' stderr=' . trim(substr($this->stderrBuf, 0, 500)));
            }
        }

        return $out;
    }

    /** Non-blocking read of everything currently available on stdout. */
    private function drain(): string
    {
        $out = '';
        while (true) {
            $chunk = fread($this->stdout, 65536);
            if ($chunk === false || $chunk === '') {
                break;
            }
            $out .= $chunk;
        }
        return $out;
    }

    /** Non-blocking read of stderr. Discarded, but read to prevent blocking. */
    private function drainStderr(): void
    {
        while (true) {
            $chunk = fread($this->stderr, 65536);
            if ($chunk === false || $chunk === '') {
                break;
            }
            $this->stderrBuf .= $chunk;
        }
    }
}


/** Parallel gzip via pigz. Produces a standard *.sql.gz consumable by any
 *  gzip-aware tool (gunzip / tar -xzf / etc.). */
class AdminerDumpPigz extends Adminer\Plugin
{
    use AdminerProcOpenCompressTrait;

    private function key(): string         { return 'pigz'; }
    private function label(): string       { return 'gzip (pigz, parallel)'; }
    private function contentType(): string { return 'application/gzip'; }

    private function isAvailable(): bool
    {
        return is_executable('/usr/bin/pigz');
    }

    private function argv(): array
    {
        // -c: write to stdout; -6: default compression level.
        // pigz autodetects the number of online processors when -p is omitted.
        return array('/usr/bin/pigz', '-c', '-6');
    }

    protected $translations = array(
        'cs' => array('gzip (pigz, parallel)' => 'gzip (pigz, paralelní)'),
        'de' => array('gzip (pigz, parallel)' => 'gzip (pigz, parallel)'),
        'fr' => array('gzip (pigz, parallel)' => 'gzip (pigz, parallèle)'),
        'es' => array('gzip (pigz, parallel)' => 'gzip (pigz, paralelo)'),
        'ru' => array('gzip (pigz, parallel)' => 'gzip (pigz, параллельный)'),
        'ja' => array('gzip (pigz, parallel)' => 'gzip (pigz、並列)'),
    );
}


/** Parallel zstd via the zstd CLI with -T0 (all cores).
 *  Distinct from `AdminerDumpZstd` (in dump-zstd.php), which uses the
 *  in-process php-ext-zstd streaming API on a single core. */
class AdminerDumpPzst extends Adminer\Plugin
{
    use AdminerProcOpenCompressTrait;

    private function key(): string         { return 'pzst'; }
    private function label(): string       { return 'Zstandard (zstd CLI, parallel)'; }
    private function contentType(): string { return 'application/zstd'; }

    private function isAvailable(): bool
    {
        return is_executable('/usr/bin/zstd');
    }

    private function argv(): array
    {
        // -c: write to stdout; -3: default level; -T0: use all online cores;
        // --no-progress: silence progress meter so stderr stays clean.
        return array('/usr/bin/zstd', '-c', '-3', '-T0', '--no-progress');
    }

    protected $translations = array(
        'cs' => array('Zstandard (zstd CLI, parallel)' => 'Zstandard (zstd CLI, paralelní)'),
        'de' => array('Zstandard (zstd CLI, parallel)' => 'Zstandard (zstd CLI, parallel)'),
        'fr' => array('Zstandard (zstd CLI, parallel)' => 'Zstandard (zstd CLI, parallèle)'),
        'es' => array('Zstandard (zstd CLI, parallel)' => 'Zstandard (zstd CLI, paralelo)'),
        'ru' => array('Zstandard (zstd CLI, parallel)' => 'Zstandard (zstd CLI, параллельный)'),
        'ja' => array('Zstandard (zstd CLI, parallel)' => 'Zstandard (zstd CLI、並列)'),
    );
}
