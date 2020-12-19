<?php

/**
 * Log file class.
 *
 * @author gharlan
 *
 * @package redaxo\core
 */
class rex_log_file implements Iterator
{
    /** @var string */
    private $path;

    /** @var resource */
    private $file;

    /** @var resource */
    private $file2;

    /** @var bool */
    private $second = false;

    /** @var int|null */
    private $pos;

    /** @var int|null */
    private $key;

    /** @var string|null */
    private $currentLine;

    /** @var string */
    private $buffer;

    /** @var int */
    private $bufferPos;

    /**
     * Constructor.
     *
     * @param string   $path        File path
     * @param int|null $maxFileSize Maximum file size
     */
    public function __construct($path, $maxFileSize = null)
    {
        $this->path = $path;
        if (!is_file($path)) {
            rex_file::put($path, '');
        }
        if ($maxFileSize && filesize($path) > $maxFileSize) {
            rename($path, $path . '.2');
        }
        $this->file = fopen($path, 'a+');
    }

    /**
     * Adds a log entry.
     *
     * @param array $data Log data
     */
    public function add(array $data)
    {
        fseek($this->file, 0, SEEK_END);
        fwrite($this->file, new rex_log_entry(time(), $data) . "\n");
    }

    /**
     * @return rex_log_entry
     */
    public function current()
    {
        return rex_log_entry::createFromString($this->currentLine);
    }

    /**
     * Reads the log file backwards line by line (each call reads one line).
     */
    public function next()
    {
        static $bufferSize = 500;

        if ($this->pos < 0) {
            // position is before file start -> look for next file
            $path2 = $this->path . '.2';
            if ($this->second || !$this->file2 && !is_file($path2)) {
                // already in file2 or file2 does not exist -> mark currentLine as invalid
                $this->currentLine = null;
                $this->key = null;
                return;
            }
            // switch to file2 and reset position
            if (!$this->file2) {
                $this->file2 = fopen($path2, 'r');
            }
            $this->second = true;
            $this->pos = null;
        }

        // get current file
        $file = $this->second ? $this->file2 : $this->file;

        if (null === $this->pos) {
            // position is not set -> set start position to start of last buffer
            fseek($file, 0, SEEK_END);
            $this->pos = (int) (ftell($file) / $bufferSize) * $bufferSize;
        }

        $line = '';
        // while position is not before file start
        while ($this->pos >= 0) {
            if ($this->bufferPos < 0) {
                // read next buffer
                fseek($file, $this->pos);
                $this->buffer = fread($file, $bufferSize);
                $this->bufferPos = strlen($this->buffer) - 1;
            }
            // read buffer backwards char by char
            for (; $this->bufferPos >= 0; --$this->bufferPos) {
                $char = $this->buffer[$this->bufferPos];
                if ("\n" === $char) {
                    // line start reached -> prepare bufferPos/pos and jump outside of while-loop
                    --$this->bufferPos;
                    if ($this->bufferPos < 0) {
                        $this->pos -= $bufferSize;
                    }
                    break 2;
                }
                if ("\r" !== $char) {
                    // build line; \r is ignored
                    $line = $char . $line;
                }
            }
            $this->pos -= $bufferSize;
        }
        if (!$line = trim($line)) {
            // empty lines are skipped -> read next line
            $this->next();
            return;
        }
        // found a non-empty line
        ++$this->key;
        $this->currentLine = $line;
    }

    /**
     * @return int|null
     */
    public function key()
    {
        return $this->key;
    }

    /**
     * {@inheritdoc}
     */
    public function valid()
    {
        return !empty($this->currentLine);
    }

    /**
     * {@inheritdoc}
     */
    public function rewind()
    {
        $this->second = false;
        $this->pos = null;
        $this->key = -1;
        $this->bufferPos = -1;
        $this->next();
    }

    /**
     * Deletes a log file and its rotations.
     *
     * @param string $path File path
     *
     * @return bool
     */
    public static function delete($path)
    {
        return rex_file::delete($path) && rex_file::delete($path . '.2');
    }
}

/**
 * Log entry class.
 *
 * @author gharlan
 *
 * @package redaxo\core
 */
class rex_log_entry
{
    public const DATE_FORMAT = 'Y-m-d H:i:s';

    /** @var int */
    private $timestamp;

    /** @var array */
    private $data;

    /**
     * Constructor.
     *
     * @param int   $timestamp Timestamp
     * @param array $data      Log data
     */
    public function __construct($timestamp, array $data)
    {
        $this->timestamp = $timestamp;
        $this->data = $data;
    }

    /**
     * Creates a log entry from string.
     *
     * @param string $string Log line
     *
     * @return rex_log_entry
     */
    public static function createFromString($string)
    {
        $data = [];
        foreach (explode(' |', $string) as $part) {
            $data[] = trim(stripcslashes($part));
        }

        $timestamp = strtotime(array_shift($data));

        return new self($timestamp, $data);
    }

    /**
     * Returns the timestamp.
     *
     * @param string $format See {@link rex_formatter::strftime}
     *
     * @return int|string Unix timestamp or formatted string if $format is given
     */
    public function getTimestamp($format = null)
    {
        if (null === $format) {
            return $this->timestamp;
        }
        return rex_formatter::strftime($this->timestamp, $format);
    }

    /**
     * Returns the log data.
     *
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        $data = array_map(static function ($part) {
            return trim(addcslashes($part, "|\n\\"));
        }, $this->data);
        $data = implode(' | ', $data);
        $data = str_replace("\r", '', $data);
        return date(self::DATE_FORMAT, $this->timestamp) . ' | ' . $data;
    }
}
