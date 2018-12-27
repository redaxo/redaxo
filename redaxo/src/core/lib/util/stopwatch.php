<?php

class rex_stopwatch
{
    /**
     * The minimum duration (in milliseconds) a callable needs to consume for beeing recorded.
     * Faster callables will not be recorded.
     *
     * @var int
     */
    const MIN_DURATION = 10;

    /**
     * List of already callables which exceeded MIN_DURATION.
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

        // dont record events which are fast as hell.
        if ($durationMs > self::MIN_DURATION) {
            $this->duration = $durationMs;

            if (isset(self::$timers[$label])) {
                $durationMs += self::$timers[$label];
            }
            self::$timers[$label] = $durationMs;
        }
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
