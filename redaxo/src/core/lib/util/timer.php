<?php

/**
 * Class to stop the script time
 *
 * @author gharlan
 *
 * @package redaxo5
 */
class rex_timer
{
  const
    SEC = 1,
    MILLISEC = 1000,
    MICROSEC = 1000000;

  private $start;

  /**
   * Constructor
   */
  public function __construct()
  {
    $this->reset();
  }

  /**
   * Resets the timer
   */
  public function reset()
  {
    $this->start = microtime(true);
  }

  /**
   * Returns the time difference
   *
   * @return float Time difference in seconds
   */
  public function getTime()
  {
    return microtime(true) - $this->start;
  }

  /**
   * Returns the formatted time difference
   *
   * @param int $precision Factor which will be multiplied, for convertion into different units (e.g. 1000 for milli,...)
   * @param int $decimals Number of decimals points
   *
   * @return string Formatted time difference
   */
  public function getFormattedTime($precision = self::SEC, $decimals = 3)
  {
    $time = $this->getTime() * $precision;
    return rex_formatter::format($time, 'number', array($decimals));
  }
}
