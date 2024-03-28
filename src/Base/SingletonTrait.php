<?php

namespace Redaxo\Core\Base;

use BadMethodCallException;

trait SingletonTrait
{
    /**
     * Singleton instances.
     *
     * @var array<class-string<static>, static>
     */
    private static array $instances = [];

    /**
     * Returns the singleton instance.
     */
    public static function getInstance(): static
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
