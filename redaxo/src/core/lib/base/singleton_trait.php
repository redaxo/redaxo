<?php

trait rex_singleton_trait
{
    /**
     * Singleton instances.
     *
     * @var array<class-string<static>, static>
     */
    private static $instances = [];

    /**
     * Returns the singleton instance.
     *
     * @return static
     */
    public static function getInstance()
    {
        $class = static::class;
        if (!isset(self::$instances[$class])) {
            self::$instances[$class] = new static();
        }
        return self::$instances[$class];
    }

    /**
     * Cloning a singleton is not allowed.
     *
     * @throws BadMethodCallException
     */
    final public function __clone()
    {
        throw new BadMethodCallException('Cloning "' . static::class . '" is not allowed!');
    }
}
