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
        if ($fp_out = gzopen($dest, $mode)) {
            if ($fp_in = fopen($source, 'r')) {
                while (!feof($fp_in)) {
                    gzwrite($fp_out, fread($fp_in, 1024 * 512));
                }
                fclose($fp_in);
            } else {
                $error = true;
            }
            gzclose($fp_out);
        } else {
            $error = true;
        }
        if ($error) {
            return false;
        }

        return $dest;
    }
}
