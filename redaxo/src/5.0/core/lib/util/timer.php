<?php

/**
 * Class to stop the script time
 *
 * @author gharlan
 *
 * @package redaxo5
 * @version svn:$Id$
 */
class rex_timer
{
  static private $instance;

  private $start;

  /**
   * Constructor
   */
  public function __construct()
  {
    $this->start();
  }

  /**
   * Returns the standard instance
   *
   * @return rex_timer
   */
  static public function getInstance()
  {
    if(!is_object(self::$instance))
    {
      self::$instance = new self();
    }
    return self::$instance;
  }

  /**
   * Starts the timer
   */
  public function start()
  {
    $this->start = microtime(true);
    echo $this->start;
  }

  /**
   * Stops the timer and returns the formatted time difference
   *
   * @param int $decimals Number of decimals points
   *
   * @return string Formatted time difference
   */
  public function stop($decimals = 3)
  {
    $time = microtime(true) - $this->start;
    return rex_formatter::format($time, 'number', array($decimals));
  }
}