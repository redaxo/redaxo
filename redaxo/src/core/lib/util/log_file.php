<?php

/**
 * Log file class
 *
 * @author gharlan
 * @package redaxo\core
 */
class rex_log_file implements Iterator
{
    private $file;
    private $pos;
    private $key;
    private $currentLine;

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
        $data = explode(' | ', $this->currentLine);
        $timestamp = strtotime(array_shift($data));
        return new rex_log_entry($timestamp, $data);
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
    private $timestamp;
    private $data;

    public function __construct($timestamp, array $data)
    {
        $this->timestamp = $timestamp;
        $this->data = $data;
    }

    public function getTimestamp($format = null)
    {
        if (is_null($format)) {
            return $this->timestamp;
        }
        return rex_formatter::strftime($this->timestamp, $format);
    }

    public function getData()
    {
        return $this->data;
    }

    public function __toString()
    {
        return date('Y-m-d H:i:s', $this->timestamp) . ' | ' . implode(' | ', $this->data);
    }
}
