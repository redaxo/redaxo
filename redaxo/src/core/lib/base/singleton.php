<?php

/**
 * Trait for singletons
 *
 * @author gharlan
 * @package redaxo\core
 */
trait rex_singleton
{
    /**
     * Singleton instances
     *
     * @var static[]
     */
    private static $instances = [];

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
