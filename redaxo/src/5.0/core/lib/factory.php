<?php

/**
 * Factory base class
 *
 * Example child class:
 * <code>
 * class example extends rex_factory
 * {
 *   private function __construct($param)
 *   {
 *     // ...
 *   }
 *
 *   static public function factory($param)
 *   {
 *   	 $class = self::getFactoryClass();
 *     return new $class($param);
 *   }
 * }
 * </code>
 *
 * @author gharlan
 */
abstract class rex_factory
{
  /**
   * @var array
   */
  static private $classes = array();

  /**
   * Sets the class for the factory
   *
   * @param string $class Classname
   */
  static public function setFactoryClass($subclass)
  {
    $calledClass = get_called_class();
    if($subclass != $calledClass && !is_subclass_of($subclass, $calledClass))
    {
      throw new rexException('$class is expected to define a subclass of '. $calledClass .'!');
    }
    self::$classes[$calledClass] = $subclass;
  }

  /**
   * Returns the class for the factory
   *
   * @return string
   */
  static public function getFactoryClass()
  {
    $calledClass = get_called_class();
    return isset(self::$classes[$calledClass]) ? self::$classes[$calledClass] : $calledClass;
  }
}