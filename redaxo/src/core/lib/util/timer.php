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

    private $start;

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

    /**
     * Resets the timer.
     */
    public function reset()
    {
        $this->start = microtime(true);
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
        return (microtime(true) - $this->start) * $precision;
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
