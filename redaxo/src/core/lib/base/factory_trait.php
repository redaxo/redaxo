<?php

/**
 * Factory trait.
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
 *
 * @package redaxo\core
 */
trait rex_factory_trait
{
    /** @var array<class-string<static>, class-string<static>> */
    private static $factoryClasses = [];

    /**
     * Sets the class for the factory.
     *
     * @param class-string<static> $subclass Classname
     * @psalm-param class-string<self> $subclass https://github.com/vimeo/psalm/issues/5535
     *
     * @throws InvalidArgumentException
     * @return void
     */
    public static function setFactoryClass($subclass)
    {
        if (!is_string($subclass)) {
            throw new InvalidArgumentException('Expecting $subclass to be a string, ' . gettype($subclass) . ' given!');
        }
        $calledClass = static::class;
        if ($subclass != $calledClass && !is_subclass_of($subclass, $calledClass)) {
            throw new InvalidArgumentException('$class "' . $subclass . '" is expected to define a subclass of ' . $calledClass . '!');
        }
        /** @psalm-suppress PropertyTypeCoercion */
        self::$factoryClasses[$calledClass] = $subclass; /** @phpstan-ignore-line */
    }

    /**
     * Returns the class for the factory. In case no factory is defined the late static binding class is returned.
     *
     * @return class-string<static>
     */
    public static function getFactoryClass()
    {
        $calledClass = static::class;
        return self::$factoryClasses[$calledClass] ?? $calledClass;
    }

    /**
     * Returns the explicitly set factory class, otherwise null.
     *
     * @return class-string<static>|null
     */
    public static function getExplicitFactoryClass(): ?string
    {
        $calledClass = static::class;
        return self::$factoryClasses[$calledClass] ?? null;
    }

    /**
     * Returns if the class has a custom factory class.
     *
     * @return bool
     */
    public static function hasFactoryClass()
    {
        $calledClass = static::class;
        return isset(self::$factoryClasses[$calledClass]) && self::$factoryClasses[$calledClass] != $calledClass;
    }

    /**
     * Calls the factory class with the given method and arguments.
     *
     * @param string $method    Method name
     * @param array  $arguments Array of arguments
     *
     * @return mixed Result of the callback
     *
     * @deprecated since 5.13, call the method on the factory class by yourself instead.
     */
    protected static function callFactoryClass($method, array $arguments)
    {
        $class = static::getFactoryClass();
        return call_user_func_array([$class, $method], $arguments);
    }
}
