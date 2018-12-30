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
    private $started = false;
    private $duration;

    /**
     * @var rex_timer
     */
    private $timer;

    /**
     * @param string $label
     */
    public function __construct($label)
    {
        $this->label = $label;
    }

    public function start()
    {
        if ($this->started) {
            throw new LogicException('can only be started once.');
        }
        $this->started = true;
        $this->timer = new rex_timer();
    }

    public function stop()
    {
        if (!$this->started) {
            throw new LogicException('missing start() call before stop().');
        }

        $label = $this->label;

        $durationMs = $this->timer->getDelta();
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
