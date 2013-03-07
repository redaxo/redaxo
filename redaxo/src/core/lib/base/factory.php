<?php

/**
 * Factory trait
 *
 * Example child class:
 * <code>
 * class rex_example
 * {
 *     use rex_factory;
 *
 *     private function __construct($param)
 *     {
 *         // ...
 *     }
 *
 *     public static function factory($param)
 *     {
 *         $class = self::getFactoryClass();
 *         return new $class($param);
 *     }
 * }
 * </code>
 *
 * @author gharlan
 * @package redaxo\core
 */
trait rex_factory
{
    /**
     * @var array
     */
    private static $factoryClasses = [];

    /**
     * Sets the class for the factory
     *
     * @param string $subclass Classname
     * @throws InvalidArgumentException
     */
    public static function setFactoryClass($subclass)
    {
        if (!is_string($subclass)) {
            throw new InvalidArgumentException('Expecting $subclass to be a string, ' . gettype($subclass) . ' given!');
        }
        $calledClass = get_called_class();
        if ($subclass != $calledClass && !is_subclass_of($subclass, $calledClass)) {
            throw new InvalidArgumentException('$class "' . $subclass . '" is expected to define a subclass of ' . $calledClass . '!');
        }
        self::$factoryClasses[$calledClass] = $subclass;
    }

    /**
     * Returns the class for the factory
     *
     * @return string
     */
    public static function getFactoryClass()
    {
        $calledClass = get_called_class();
        return isset(self::$factoryClasses[$calledClass]) ? self::$factoryClasses[$calledClass] : $calledClass;
    }

    /**
     * Returns if the class has a custom factory class
     *
     * @return boolean
     */
    public static function hasFactoryClass()
    {
        $calledClass = get_called_class();
        return isset(self::$factoryClasses[$calledClass]) && self::$factoryClasses[$calledClass] != $calledClass;
    }

    /**
     * Calls the factory class with the given method and arguments
     *
     * @param string $method    Method name
     * @param array  $arguments Array of arguments
     * @return mixed Result of the callback
     */
    protected static function callFactoryClass($method, array $arguments)
    {
        $class = static::getFactoryClass();
        return call_user_func_array([$class, $method], $arguments);
    }
}
