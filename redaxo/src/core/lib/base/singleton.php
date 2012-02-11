<?php

/**
 * Base class for singletons
 *
 * @author gharlan
 */
abstract class rex_singleton
{
  /**
   * Singleton instances
   *
   * @var array[rex_singleton];
   */
  static private $instances = array();

  /**
   * Returns the singleton instance
   *
   * @return rex_singleton
   */
  static public function getInstance()
  {
    $class = get_called_class();
    if(!isset(self::$instances[$class]))
    {
      self::$instances[$class] = new static;
    }
    return self::$instances[$class];
  }
}
