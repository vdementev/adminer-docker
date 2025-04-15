<?php

/** Dump compressed with Zstandard (.zst)
 * @link https://www.adminer.org/plugins/#use
 * @uses zstd extension (https://pecl.php.net/package/zstd), function zstd_compress()
 * @author Adapted by AI from AdminerDumpZip by Jakub Vrana, https://www.vrana.cz/
 * @license https://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
 * @license https://www.gnu.org/licenses/gpl-2.0.html GNU General Public License, version 2 (one or other)
 */
class AdminerDumpZstd
{
    /** @access protected */
    var $data = '';
    /** @access protected */
    var $filename_base = ''; // Base filename for Content-Disposition

    /**
     * Adds 'Zstandard (.zst)' option to the output format dropdown if zstd is available.
     * @return array Associative array with 'zstd' key if supported, empty array otherwise.
     */
    function dumpOutput()
    {
        // Check if the zstd compression function exists (requires php-zstd extension)
        if (!function_exists('zstd_compress')) {
            return array(); // zstd not available
        }
        // Add the option to the dropdown
        return array('zstd' => 'Zstandard (.zst)');
    }

    /**
     * Output buffer callback handler for zstd compression.
     * Accumulates data and compresses it at the end.
     * @param string $string Chunk of data from the output buffer.
     * @param int $state Output buffer state flags (e.g., PHP_OUTPUT_HANDLER_END).
     * @return string Compressed data on end, empty string otherwise, or error message on failure.
     */
    function _zstd($string, $state)
    {
        $this->data .= $string; // Append current chunk to accumulated data

        // Check if this is the final call to the buffer handler
        if ($state & PHP_OUTPUT_HANDLER_END) {
            // Double-check function existence (should be caught by dumpOutput, but good practice)
            if (!function_exists('zstd_compress')) {
                 // This shouldn't normally happen if dumpOutput worked
                error_log("AdminerDumpZstd: zstd_compress() function disappeared unexpectedly.");
                return "Error: zstd_compress() function not found during compression.";
            }

            // Compress the accumulated data
            $compressedData = zstd_compress($this->data);

            // Check if compression failed
            if ($compressedData === false) {
                error_log("AdminerDumpZstd: zstd_compress() failed.");
                // Return an error message instead of corrupted data
                return "Error: Zstandard compression failed.";
            }

            // Clear the buffer data to free memory
            $this->data = '';

            // Return the compressed data
            return $compressedData;
        }

        // For intermediate calls, return an empty string as required by ob_start callback
        return "";
    }

    /**
     * Sets HTTP headers for zstd download and starts output buffering.
     * @param string $identifier Default filename base (e.g., database name or table name).
     * @param bool $multi_table Indicates if multiple tables are being dumped (affects naming for csv/tsv).
     * @return null
     */
    function dumpHeaders($identifier, $multi_table = false)
    {
        // Check if 'zstd' output format was selected in the form
        if (isset($_POST["output"]) && $_POST["output"] == "zstd") {

             // Determine the original format/extension (sql, csv, tsv)
            $format = isset($_POST["format"]) ? $_POST["format"] : 'sql'; // Default to sql

            // Handle the special case for multi-table CSV/TSV dumps (they become .tar)
            // This mirrors the logic in the original AdminerDumpZip
            $extension = ($multi_table && preg_match("~^[ct]sv$~", $format)) ? "tar" : $format;

            // Construct the base filename (e.g., "database.sql" or "database.tar")
            $this->filename_base = "$identifier.$extension";

            // Set the appropriate Content-Type header for zstd
            header("Content-Type: application/zstd");

            // Set Content-Disposition to suggest a filename for the browser's download dialog
            header("Content-Disposition: attachment; filename=\"" . $this->filename_base . ".zst\"");

            // Start output buffering, capturing all output and passing it to the _zstd method
            ob_start(array($this, '_zstd'));
        }
    }
}

?>