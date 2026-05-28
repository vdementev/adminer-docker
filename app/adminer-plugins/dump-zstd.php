<?php

/** Streaming Zstandard (zstd) export
 *
 * Uses the php-ext-zstd streaming context API (`zstd_compress_init` /
 * `zstd_compress_add`) wired into an output-buffer callback so SQL is
 * compressed chunk-by-chunk as Adminer emits it, never accumulating the
 * whole dump in PHP memory.
 *
 * Notes:
 *  - `ob_zstd_handler` in php-ext-zstd 0.15.x is a passthrough (does not
 *    actually compress); we must drive the streaming API ourselves.
 *  - `dumpHeaders()` MUST return a non-empty string equal to the underlying
 *    format identifier ("sql"|"csv"|...). Adminer compares this with `==`
 *    against "tar" elsewhere; returning a bool triggers PHP loose-equality
 *    `true == "tar"` and Adminer enters tar-archive mode, wrapping the dump
 *    in 512-byte tar headers.
 *
 * zstd at default level ≈ 5–10× faster than PHP's single-threaded gzip
 * (`gzencode`) and produces a slightly denser file — suitable for multi-GB
 * dumps over slow links without changing PHP/Angie/MySQL timeouts.
 *
 * @link https://www.adminer.org/plugins/#use
 * @uses zstd_compress_init / zstd_compress_add (kjdev/php-ext-zstd ≥ 0.13)
 * @author Jakub Vrana (original ZIP plugin), zstd port by Vasilii Dementev
 * @license https://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
 * @license https://www.gnu.org/licenses/gpl-2.0.html GNU General Public License, version 2 (one or other)
 */
class AdminerDumpZstd extends Adminer\Plugin
{
    /** zstd streaming context, alive from dumpHeaders() until PHP_OUTPUT_HANDLER_FINAL */
    private $ctx = null;

    /** Compression level. 3 = zstd default; ~zlib level 7 ratio at ~5× speed. */
    private $level = 3;

    /** Add a "Zstandard" option to the Output radio group.
     *  Value is "zst" so Adminer's filename builder appends ".zst" (the
     *  conventional file extension). */
    function dumpOutput()
    {
        if (!function_exists('zstd_compress_init')) {
            return array();
        }
        return array('zst' => 'Zstandard (zst)');
    }

    /**
     * Set Content-Type and start the streaming compressor.
     *
     * @return string|null The format identifier ("sql"/"csv"/...) so Adminer
     *                     knows what extension to embed in the filename
     *                     (`<identifier>.<format>.<output>`), and so that
     *                     loose comparisons against "tar" stay false.
     *                     Returning null defers to the default handler.
     */
    function dumpHeaders($identifier, $multi_table = false)
    {
        if (($_POST['output'] ?? '') !== 'zst') {
            return null;
        }

        // Mirror Adminer's default format resolution.
        $format = preg_match('~sql~', $_POST['format'] ?? '')
            ? 'sql'
            : ($multi_table ? 'tar' : 'csv');

        header('Content-Type: application/zstd');
        // Adminer will set Content-Disposition itself, using `<dumpFilename>.<format>.zst`.

        $this->ctx = zstd_compress_init($this->level);

        self::tuneDumpSession();

        // Trigger the callback every 64 KiB so memory stays bounded.
        ob_start(array($this, 'compressChunk'), 65536);

        return $format;
    }

    /**
     * Per-session MySQL pragmas to skip work we don't need during a dump.
     * Mirrors `AdminerDumpProcOpenBase::tuneDumpSession()` — see that file
     * for the rationale. Kept duplicated rather than via a shared trait
     * because Adminer's `Plugins` constructor auto-instantiates every
     * declared subclass of `Adminer\Plugin`, and an abstract shared base
     * class would error out.
     */
    private static function tuneDumpSession(): void
    {
        $conn = Adminer\connection();
        if (!$conn) {
            return;
        }
        @$conn->query('SET SESSION slow_query_log = 0');
        @$conn->query('SET SESSION long_query_time = 31536000');
        @$conn->query('SET SESSION sql_log_off = 1');
        @$conn->query('SET SESSION sql_log_bin = 0');
        @$conn->query('SET SESSION net_read_timeout = 14400');
        @$conn->query('SET SESSION net_write_timeout = 14400');
        @$conn->query('SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ');
        @$conn->query('START TRANSACTION WITH CONSISTENT SNAPSHOT');
    }

    /** ob_start callback: compress each chunk via the streaming context. */
    function compressChunk($string, $state)
    {
        if ($this->ctx === null) {
            return $string;
        }

        // `end=false` keeps the frame open; on FINAL we issue a separate
        // `end=true` call that writes the zstd epilogue + checksum.
        // (php-ext-zstd 0.15: empty input alone is a no-op; you must pass
        //  the bool `end` parameter to terminate the stream.)
        $out = ($string === '') ? '' : zstd_compress_add($this->ctx, $string, false);

        if ($state & PHP_OUTPUT_HANDLER_FINAL) {
            $out .= zstd_compress_add($this->ctx, '', true);
            $this->ctx = null;
        }

        return $out;
    }

    protected $translations = array(
        'cs' => array('Zstandard (zst)' => 'Zstandard komprese'),
        'de' => array('Zstandard (zst)' => 'Zstandard Kompression'),
        'fr' => array('Zstandard (zst)' => 'Compression Zstandard'),
        'es' => array('Zstandard (zst)' => 'Compresión Zstandard'),
        'pl' => array('Zstandard (zst)' => 'Kompresja Zstandard'),
        'ru' => array('Zstandard (zst)' => 'Сжатие Zstandard'),
        'ja' => array('Zstandard (zst)' => 'Zstandard 圧縮'),
        'zh' => array('Zstandard (zst)' => 'Zstandard 压缩'),
    );
}
