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
    const SEC = 1;
    const MILLISEC = 1000;
    const MICROSEC = 1000000;

    public static $serverTimings = [];

    private $label;
    private $start;
    private $duration;

    /**
     * Constructor.
     *
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
        $timer->label = $label;
        $result = $callable();

        $timer->stop();

        return $result;
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

        $label = $this->label;

        if (null === $label) {
            return;
        }

        $duration = isset(self::$timers[$label]) ? self::$timers[$label] : 0;
        $duration += $this->duration * self::MILLISEC;

        self::$timers[$label] = $duration;
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
