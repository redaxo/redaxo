<?php

/**
 * Log file class
 *
 * @author gharlan
 * @package redaxo\core
 */
class rex_log_file implements Iterator
{
    /**
     * @var string
     */
    private $path;

    /**
     * @var resource
     */
    private $file;

    /**
     * @var resource
     */
    private $file2;

    /**
     * @var bool
     */
    private $second = false;

    /**
     * @var int
     */
    private $pos;

    /**
     * @var int
     */
    private $key;

    /**
     * @var string
     */
    private $currentLine;

    /**
     * Constructor
     *
     * @param string   $path        File path
     * @param int|null $maxFileSize Maximum file size
     */
    public function __construct($path, $maxFileSize = null)
    {
        $this->path = $path;
        if ($maxFileSize && filesize($path) > $maxFileSize) {
            rename($path, $path . '.2');
        }
        $this->file = fopen($path, 'a+b');
    }

    /**
     * Adds a log entry
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
     * {@inheritDoc}
     */
    public function next()
    {
        $file = $this->second ? $this->file2 : $this->file;
        if (-1 === fseek($file, $this->pos, SEEK_END)) {
            $path2 = $this->path . '.2';
            if (!$this->second && ($this->file2 || file_exists($path2))) {
                if (!$this->file2) {
                    $this->file2 = fopen($path2, 'rb');
                }
                $this->second = true;
                $this->pos = 0;
                $this->next();
                return;
            }
            $this->currentLine = null;
            $this->key = null;
            return;
        }
        $line = '';
        do {
            $char = fgetc($file);
            if ("\n" !== $char) {
                if ("\r" !== $char) {
                    $line = $char . $line;
                }
            } elseif ($line = trim($line)) {
                break;
            }
            $this->pos--;
        } while (-1 !== fseek($file, $this->pos, SEEK_END));
        $this->key++;
        $this->currentLine = $line;
    }

    /**
     * {@inheritDoc}
     */
    public function key()
    {
        return $this->key;
    }

    /**
     * {@inheritDoc}
     */
    public function valid()
    {
        return !empty($this->currentLine);
    }

    /**
     * {@inheritDoc}
     */
    public function rewind()
    {
        $this->second = false;
        $this->pos = 0;
        $this->key = -1;
        $this->next();
    }

    /**
     * Deletes a log file
     *
     * @param string $path File path
     * @return bool
     */
    public static function delete($path)
    {
        return rex_file::delete($path) && rex_file::delete($path . '.2');
    }
}

/**
 * Log entry class
 *
 * @author gharlan
 * @package redaxo\core
 */
class rex_log_entry
{
    /**
     * @var int
     */
    private $timestamp;

    /**
     * @var array
     */
    private $data;

    /**
     * Constructor
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
     * Creates a log entry from string
     *
     * @param string $string Log line
     * @return rex_log_entry
     */
    public static function createFromString($string)
    {
        $data = array_map('trim', explode(' | ', $string));
        $timestamp = strtotime(array_shift($data));
        return new self($timestamp, $data);
    }

    /**
     * Returns the timestamp
     *
     * @param string $format See {@link rex_formatter::strftime}
     * @return int|string Unix timestamp or formatted string if $format is given
     */
    public function getTimestamp($format = null)
    {
        if (is_null($format)) {
            return $this->timestamp;
        }
        return rex_formatter::strftime($this->timestamp, $format);
    }

    /**
     * Returns the log data
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
        $data = implode(' | ', array_map('trim', $this->data));
        $data = str_replace(["\r", "\n"], '', $data);
        return date('Y-m-d H:i:s', $this->timestamp) . ' | ' . $data;
    }
}
