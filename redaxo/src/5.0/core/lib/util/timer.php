<?php

/**
 * Class to stop the script time
 *
 * @author gharlan
 *
 * @package redaxo5
 * @version svn:$Id$
 */
class rex_timer extends rex_singleton
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
    $this->start();
  }

  /**
   * Starts the timer
   */
  public function start()
  {
    $this->start = microtime(true);
  }

  /**
   * Stops the timer and returns the formatted time difference
   *
   * @param int $precision Factor which will be multiplied, for convertion into different units (e.g. 1000 for milli,...)
   * @param int $decimals Number of decimals points
   *
   * @return string Formatted time difference
   */
  public function stop($precision = self::SEC, $decimals = 3)
  {
    $time = (microtime(true) - $this->start) * $precision;
    return rex_formatter::format($time, 'number', array($decimals));
  }
}