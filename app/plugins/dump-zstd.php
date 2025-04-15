<?php

/** Dump using Zstandard (zstd) compression
 * @link https://www.adminer.org/plugins/#use
 * @uses zstd_compress() (requires php-zstd PECL extension)
 * @author Jakub Vrana (original ZIP plugin), Modified for Zstd by Vasilii Dementev
 * @license https://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
 * @license https://www.gnu.org/licenses/gpl-2.0.html GNU General Public License, version 2 (one or other)
 */
class AdminerDumpZstd extends Adminer\Plugin
{
    protected $data = ''; // Property to accumulate dump data

    /**
     * Add Zstandard option to the export output format list.
     *
     * @return array Associative array with 'zstd' key if zstd_compress function exists.
     */
    function dumpOutput()
    {
        if (!function_exists('zstd_compress')) {
            return array();
        }
        return array('zstd' => 'Zstandard');
    }

    /**
     * Callback function for output buffering to compress the data using zstd.
     *
     * @param string $string Chunk of data from the output buffer.
     * @param int $state Output buffer state flags (e.g., PHP_OUTPUT_HANDLER_START, PHP_OUTPUT_HANDLER_END).
     * @return string Compressed data when buffer ends, empty string otherwise. Returns false on compression error.
     */
    function _compressZstd($string, $state)
    {
        $this->data .= $string;
        if ($state & PHP_OUTPUT_HANDLER_END) {
            $compressed_data = zstd_compress($this->data);
            $this->data = '';
            if ($compressed_data === false) {
                error_log("AdminerDumpZstd: zstd_compress failed.");
                return false;
            }
            return $compressed_data;
        }
        return "";
    }

    /**
     * Set appropriate headers for zstd download and start output buffering.
     *
     * @param string $identifier Base filename (usually database or table name).
     * @param bool $multi_table Whether multiple tables are being dumped (affects CSV/TSV extension).
     * @return null|bool Returns null if the output format is not 'zstd', potentially bool from ob_start.
     */
    function dumpHeaders($identifier, $multi_table = false)
    {
        if (isset($_POST["output"]) && $_POST["output"] == "zstd") {
            $extension = (isset($_POST["format"]) ? $_POST["format"] : 'sql');
            if ($multi_table && preg_match("~^[ct]sv~", $extension)) {
                $extension = "tar"; // Although zstd compresses the tar, not creates it here
            }
            $filename = "$identifier.$extension.zst";
            header("Content-Type: application/zstd");
            // Set the Content-Disposition header to suggest the filename to the browser
            // Use 'attachment' to force download
            header("Content-Disposition: attachment; filename=\"" . addslashes($filename) . "\"");
            $this->data = '';
            return ob_start(array($this, '_compressZstd'));
        }
        return null;
    }

    /**
     * Translations for the UI element.
     */
    protected $translations = array(
        'cs' => array('Zstandard' => 'Zstandard komprese'), // Czech
        'de' => array('Zstandard' => 'Zstandard Kompression'), // German
        'fr' => array('Zstandard' => 'Compression Zstandard'), // French
        'es' => array('Zstandard' => 'Compresión Zstandard'), // Spanish
        'pl' => array('Zstandard' => 'Kompresja Zstandard'),    // Polish
        'ru' => array('Zstandard' => 'Сжатие Zstandard'),      // Russian
        'ja' => array('Zstandard' => 'Zstandard 圧縮'),        // Japanese
        'zh' => array('Zstandard' => 'Zstandard 压缩'),        // Chinese
    );
}
