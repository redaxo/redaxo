<?php

/**
 * Class for handling files.
 *
 * @package redaxo\core
 */
class rex_file
{
    /**
     * Returns the content of a file.
     *
     * @param string $file    Path to the file
     * @param mixed  $default Default value
     *
     * @return mixed Content of the file or default value if the file isn't readable
     */
    public static function get($file, $default = null)
    {
        $content = @file_get_contents($file);
        return $content !== false ? $content : $default;
    }

    /**
     * Returns the content of a config file.
     *
     * @param string $file    Path to the file
     * @param mixed  $default Default value
     *
     * @return mixed Content of the file or default value if the file isn't readable
     */
    public static function getConfig($file, $default = [])
    {
        $content = self::get($file);
        return $content === null ? $default : rex_string::yamlDecode($content);
    }

    /**
     * Returns the content of a cache file.
     *
     * @param string $file    Path to the file
     * @param mixed  $default Default value
     *
     * @return mixed Content of the file or default value if the file isn't readable
     */
    public static function getCache($file, $default = [])
    {
        $content = self::get($file);
        return $content === null ? $default : json_decode($content, true);
    }

    /**
     * Puts content in a file.
     *
     * @param string $file    Path to the file
     * @param string $content Content for the file
     *
     * @return bool TRUE on success, FALSE on failure
     */
    public static function put($file, $content)
    {
        if (!rex_dir::create(dirname($file)) || file_exists($file) && !is_writable($file)) {
            return false;
        }

        if (file_put_contents($file, $content) !== false) {
            @chmod($file, rex::getFilePerm());
            return true;
        }

        return false;
    }

    /**
     * Puts content in a config file.
     *
     * @param string $file    Path to the file
     * @param mixed  $content Content for the file
     * @param int    $inline  The level where you switch to inline YAML
     *
     * @return bool TRUE on success, FALSE on failure
     */
    public static function putConfig($file, $content, $inline = 3)
    {
        return self::put($file, rex_string::yamlEncode($content, $inline));
    }

    /**
     * Puts content in a cache file.
     *
     * @param string $file    Path to the file
     * @param mixed  $content Content for the file
     *
     * @return bool TRUE on success, FALSE on failure
     */
    public static function putCache($file, $content)
    {
        return self::put($file, json_encode($content));
    }

    /**
     * Copies a file.
     *
     * @param string $srcfile Path of the source file
     * @param string $dstfile Path of the destination file or directory
     *
     * @return bool TRUE on success, FALSE on failure
     */
    public static function copy($srcfile, $dstfile)
    {
        if (is_file($srcfile)) {
            if (is_dir($dstfile)) {
                $dstdir = rtrim($dstfile, DIRECTORY_SEPARATOR);
                $dstfile = $dstdir . DIRECTORY_SEPARATOR . basename($srcfile);
            } else {
                $dstdir = dirname($dstfile);
                rex_dir::create($dstdir);
            }

            if (rex_dir::isWritable($dstdir) && (!file_exists($dstfile) || is_writable($dstfile)) && copy($srcfile, $dstfile)) {
                @chmod($dstfile, rex::getFilePerm());
                touch($dstfile, filemtime($srcfile), fileatime($srcfile));
                return true;
            }
        }
        return false;
    }

    /**
     * Deletes a file.
     *
     * @param string $file Path of the file
     *
     * @return bool TRUE on success, FALSE on failure
     */
    public static function delete($file)
    {
        if (file_exists($file)) {
            return unlink($file);
        }
        return true;
    }

    /**
     * Extracts the extension of the given filename.
     *
     * @param string $filename Filename
     *
     * @return string Extension of $filename
     */
    public static function extension($filename)
    {
        return pathinfo($filename, PATHINFO_EXTENSION);
    }

    /**
     * Formates the filesize of the given file into a userfriendly form.
     *
     * @param string $file   Path to the file
     * @param array  $format
     *
     * @return string Formatted filesize
     */
    public static function formattedSize($file, $format = [])
    {
        return rex_formatter::bytes(filesize($file), $format);
    }

    /**
     * Gets executed content of given file.
     *
     * @param string $file Path of the file
     *
     * @return string executed Content
     */
    public static function getOutput($file)
    {
        ob_start();
        require $file;
        return ob_get_clean();
    }
}
