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
        static $enabled = false;

        // we might get called very early in the process, in which case we can't determine yet whether the user is logged in.
        // this also means, in debug-mode we get more timings in comparison to admin-only timings.
        if (!$enabled) {
            // dont create the user (can cause session locks), to prevent influencing the things we try to measure.
            $enabled = rex::isDebugMode() || ($user = rex::getUser()) && $user->isAdmin();
        }

        if (!$enabled) {
            return $callable();
        }

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
