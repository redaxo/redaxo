<?php

/**
 * Class to stop the script time.
 *
 * @author gharlan
 *
 * @package redaxo\core
 */
class rex_timer
{
    public const SEC = 1;
    public const MILLISEC = 1000;
    public const MICROSEC = 1000000;

    /**
     * @var array
     * @psalm-var array<string, float>
     */
    public static $serverTimings = [];

    private $start;
    private $duration;

    /**
     * @param float $start Start time
     */
    public function __construct($start = null)
    {
        if ($start) {
            $this->start = $start;
        } else {
            $this->reset();
        }
    }

    /**
     * Measures the runtime of the given callable.
     *
     * On sufficient user permissions - or in debug mode - this timings will be sent over the wire to the browser via server timing api http headers.
     *
     * @param string $label
     *
     * @return mixed result of callable
     */
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

        $timer = new self();

        try {
            return $callable();
        } finally {
            $timer->stop();

            $duration = isset(self::$serverTimings[$label]) ? self::$serverTimings[$label] : 0;
            $duration += $timer->getDelta(self::MILLISEC);

            self::$serverTimings[$label] = $duration;
        }
    }

    /**
     * Resets the timer.
     */
    public function reset()
    {
        $this->start = microtime(true);
    }

    /**
     * Stops the timer.
     */
    public function stop()
    {
        $this->duration = microtime(true) - $this->start;
    }

    /**
     * Returns the time difference.
     *
     * @param int $precision Factor which will be multiplied, for convertion into different units (e.g. 1000 for milli,...)
     *
     * @return float Time difference
     */
    public function getDelta($precision = self::MILLISEC)
    {
        $duration = null === $this->duration ? microtime(true) - $this->start : $this->duration;

        return $duration * $precision;
    }

    /**
     * Returns the formatted time difference.
     *
     * @param int $precision Factor which will be multiplied, for convertion into different units (e.g. 1000 for milli,...)
     * @param int $decimals  Number of decimals points
     *
     * @return string Formatted time difference
     */
    public function getFormattedDelta($precision = self::MILLISEC, $decimals = 3)
    {
        $time = $this->getDelta($precision);
        return rex_formatter::number($time, [$decimals]);
    }
}
