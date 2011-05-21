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
    if(!is_string($subclass))
    {
      throw new rex_exception('Expecting $subclass to be a string, '. gettype($subclass) . ' given!');
    }
    $calledClass = get_called_class();
    if($subclass != $calledClass && !is_subclass_of($subclass, $calledClass))
    {
      throw new rex_exception('$class "'. $subclass .'" is expected to define a subclass of '. $calledClass .'!');
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

  /**
   * Returns if the class has a custom factory class
   *
   * @return boolean
   */
  static public function hasFactoryClass()
  {
    $calledClass = get_called_class();
    return isset(self::$classes[$calledClass]) && self::$classes[$calledClass] != $calledClass;
  }

  /**
   * Calls the factory class with the given method and arguments
   *
   * @param string $method Method name
   * @param array $arguments Array of arguments
   * @return mixed Result of the callback
   */
  static protected function callFactoryClass($method, array $arguments)
  {
    $class = static::getFactoryClass();
    return call_user_func_array(array($class, $method), $arguments);
  }
}