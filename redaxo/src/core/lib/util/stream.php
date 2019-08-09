<?php

/**
 * Stream wrapper to include variables like files (php code will be evaluated).
 *
 * Example:
 * <code>
 * <?php
 *   include rex_stream::factory('myContent', '<?php echo "Hello World"; ?>');
 * ?>
 * </code>
 *
 * @author gharlan
 *
 * @package redaxo\core
 *
 * @see http://www.php.net/manual/en/class.streamwrapper.php
 */
class rex_stream
{
    private static $useRealFiles;

    private static $registered = false;
    private static $nextContent = [];

    private $position;
    private $content;

    /**
     * Prepares a new stream.
     *
     * @param string $path    Virtual path which should describe the content (e.g. "template/1"), only relevant for error messages
     * @param string $content Content which will be included
     *
     * @throws InvalidArgumentException
     *
     * @return string Full path with protocol (e.g. "rex:///template/1")
     */
    public static function factory($path, $content)
    {
        if (!is_string($path) || empty($path)) {
            throw new InvalidArgumentException('Expecting $path to be a string and not empty!');
        }
        if (!is_string($content)) {
            throw new InvalidArgumentException('Expecting $content to be a string!');
        }

        if (null === self::$useRealFiles) {
            self::$useRealFiles = extension_loaded('suhosin')
                && !preg_match('/(?:^|,)rex(?::|,|$)/', ini_get('suhosin.executor.include.whitelist'));
        }

        if (self::$useRealFiles) {
            $hash = substr(sha1($content), 0, 7);
            $path = rex_path::coreCache('stream/'.$path.'/'.$hash);

            if (!file_exists($path)) {
                rex_file::put($path, $content);
            }

            return $path;
        }

        if (!self::$registered) {
            stream_wrapper_register('rex', self::class);
            self::$registered = true;
        }

        // 3 slashes needed to sidestep some server url include protections
        // example: https://www.strato.de/faq/article/622/Warum-erhalte-ich-über-PHP-die-Fehlermeldung-%22Warning:-main()-…:-include(….).html
        $path = 'rex:///' . $path;
        self::$nextContent[$path] = $content;

        return $path;
    }

    /**
     * @see http://www.php.net/manual/en/streamwrapper.stream-open.php
     */
    public function stream_open($path, $mode, $options, &$opened_path)
    {
        if (!isset(self::$nextContent[$path]) || !is_string(self::$nextContent[$path])) {
            return false;
        }

        $this->position = 0;
        $this->content = self::$nextContent[$path];
        //unset(self::$nextContent[$path]);

        return true;
    }

    /**
     * @see http://www.php.net/manual/en/streamwrapper.stream-read.php
     */
    public function stream_read($count)
    {
        $ret = substr($this->content, $this->position, $count);
        $this->position += strlen($ret);
        return $ret;
    }

    /**
     * @see http://www.php.net/manual/en/streamwrapper.stream-eof.php
     */
    public function stream_eof()
    {
        return $this->position >= strlen($this->content);
    }

    /**
     * @see http://www.php.net/manual/en/streamwrapper.stream-seek.php
     */
    public function stream_seek($offset, $whence = SEEK_SET)
    {
        switch ($whence) {
            case SEEK_SET:
                $this->position = $offset;
                return true;
            case SEEK_CUR:
                $this->position += $offset;
                return true;
            case SEEK_END:
                $this->position = strlen($this->content) - 1 + $offset;
                return true;
            default:
                return false;
        }
    }

    /**
     * @see http://www.php.net/manual/en/streamwrapper.stream-set-option.php
     */
    public function stream_set_option()
    {
        return false;
    }

    /**
     * @see http://www.php.net/manual/en/streamwrapper.stream-tell.php
     */
    public function stream_tell()
    {
        return $this->position;
    }

    /**
     * @see http://www.php.net/manual/en/streamwrapper.stream-flush.php
     */
    public function stream_flush()
    {
        return true;
    }

    /**
     * @see http://www.php.net/manual/en/streamwrapper.stream-stat.php
     */
    public function stream_stat()
    {
        return null;
    }

    /**
     * @see http://www.php.net/manual/en/streamwrapper.url-stat.php
     */
    public function url_stat()
    {
        return null;
    }
}
