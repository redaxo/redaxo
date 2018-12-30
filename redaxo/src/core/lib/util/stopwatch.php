<?php

/**
 * @package redaxo\core
 */
class rex_stopwatch
{
    /**
     * List of fired callables.
     *
     * @var float[]
     */
    public static $timers = [];

    private $label;

    private $start;

    private $duration;

    /**
     * @param string $label
     */
    public function __construct($label)
    {
        $this->label = $label;
    }

    public function start()
    {
        if ($this->start) {
            throw new LogicException('can only be started once.');
        }
        $this->start = microtime(true);
    }

    public function stop()
    {
        if (!$this->start) {
            throw new LogicException('missing start() call before stop().');
        }

        $start = $this->start;
        $label = $this->label;

        $durationSec = microtime(true) - $start;
        $durationMs = $durationSec * 1000;
        $this->duration = $durationMs;

        if (isset(self::$timers[$label])) {
            $durationMs += self::$timers[$label];
        }
        self::$timers[$label] = $durationMs;
    }

    public static function measure($label, callable $callable)
    {
        $watch = new self($label);

        $watch->start();
        $result = $callable();
        $watch->stop();

        return $result;
    }

    /**
     * Returns the measured duration in milliseconds.
     *
     * @return float
     */
    public function getDuration()
    {
        return $this->duration;
    }
}
