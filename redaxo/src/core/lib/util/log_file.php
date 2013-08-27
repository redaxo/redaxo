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
     * @var SplFileObject
     */
    private $file;

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
     * @param int      $deleteLines Amount of lines which will be deleted if $maxFileSize is reached
     */
    public function __construct($path, $maxFileSize = null, $deleteLines = 10000)
    {
        $this->file = new SplFileObject($path, 'a+b');
        if ($maxFileSize && $this->file->getSize() > $maxFileSize) {
            $temp = new SplTempFileObject();
            foreach (new LimitIterator($this->file, $deleteLines) as $line) {
                $temp->fwrite($line);
            }
            $this->file->ftruncate(0);
            foreach ($temp as $line) {
                $this->file->fwrite($line);
            }
        }
    }

    /**
     * Adds a log entry
     *
     * @param array $data Log data
     */
    public function add(array $data)
    {
        $this->file->fseek(0, SEEK_END);
        $this->file->fwrite(new rex_log_entry(time(), $data) . "\n");
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
        if (-1 === $this->file->fseek($this->pos, SEEK_END)) {
            $this->currentLine = null;
            $this->key = null;
            return;
        }
        $line = '';
        do {
            $char = $this->file->fgetc();
            if ("\n" !== $char) {
                if ("\r" !== $char) {
                    $line = $char . $line;
                }
            } elseif ($line = trim($line)) {
                break;
            }
            $this->pos--;
        } while (-1 !== $this->file->fseek($this->pos, SEEK_END));
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
        $this->file->fseek(0, SEEK_END);
        $this->pos = 0;
        $this->key = -1;
        $this->next();
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
