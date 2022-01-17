<?php

/**
 * @package redaxo\backup
 *
 * @internal
 */
class rex_backup_file_compressor
{
    /**
     * GZIPs a file on disk (appending .gz to the name).
     *
     * From http://stackoverflow.com/questions/6073397/how-do-you-create-a-gz-file-using-php
     * Based on function by Kioob at:
     * http://www.php.net/manual/en/function.gzwrite.php#34955
     *
     * @param string $source Path to file that should be compressed
     * @param int $level GZIP compression level (default: 9)
     * @return string|false New filename (with .gz appended) if success, or false if operation fails
     */
    public function gzCompress(string $source, int $level = 9)
    {
        $dest = $source . '.gz';
        $mode = 'wb' . $level;
        $error = false;
        if ($fpOut = gzopen($dest, $mode)) {
            if ($fpIn = fopen($source, 'r')) {
                while (!feof($fpIn)) {
                    gzwrite($fpOut, fread($fpIn, 1024 * 512));
                }
                fclose($fpIn);
            } else {
                $error = true;
            }
            gzclose($fpOut);
        } else {
            $error = true;
        }
        if ($error) {
            return false;
        }

        return $dest;
    }

    /**
     * Read a gz compressed file into a plain string.
     *
     * @param string $source Path to a .gz file that should be decompressed
     * @return string|false The uncompressed content if success, or false if operation fails
     */
    public function gzReadDeCompressed(string $source)
    {
        if ('gz' !== rex_file::extension($source)) {
            throw new Exception('Expecting a file with .gz suffix');
        }

        $str = '';
        if ($fpIn = gzopen($source, 'r')) {
            while (!gzeof($fpIn)) {
                $str .= gzgets($fpIn, 1024 * 512);
            }
            gzclose($fpIn);
        } else {
            return false;
        }

        return $str;
    }
}
