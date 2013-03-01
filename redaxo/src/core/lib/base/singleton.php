<?php

/**
 * Base class for singletons
 *
 * @author gharlan
 * @package redaxo\core
 */
abstract class rex_singleton_base
{
    /**
     * Singleton instances
     *
     * @var rex_singleton_base[]
     */
    private static $instances = array();

    /**
     * Returns the singleton instance
     *
     * @return static
     */
    public static function getInstance()
    {
        $class = get_called_class();
        if (!isset(self::$instances[$class])) {
            self::$instances[$class] = new static;
        }
        return self::$instances[$class];
    }
}
