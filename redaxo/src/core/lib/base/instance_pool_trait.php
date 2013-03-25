<?php

/**
 * Instance Pool Trait
 *
 * @author gharlan
 * @package redaxo\core
 */
trait rex_instance_pool_trait
{
    /**
     * @var static[]
     */
    private static $instances = [];

    /**
     * Constructor
     */
    private function __construct()
    {
        // noop
    }

    /**
     * Adds an instance
     *
     * @param self  $instance Instance
     * @param mixed $key      Key
     * @param mixed $key,...  Unlimited optional number of additional keys
     */
    protected static function addInstance(self $instance, $key)
    {
        $key = self::getKey(array_slice(func_get_args(), 1));
        self::$instances[get_called_class()][$key] = $instance;
    }

    /**
     * Checks whether an instance exists for the given key
     *
     * @param mixed $key     Key
     * @param mixed $key,... Unlimited optional number of additional keys
     * @return bool
     */
    protected static function hasInstance($key)
    {
        return isset(self::$instances[get_called_class()][self::getKey(func_get_args())]);
    }

    /**
     * Returns the instance for the given key
     *
     * @param mixed $key     Key
     * @param mixed $key,... Unlimited optional number of additional keys
     * @return null|static
     */
    protected static function getInstance($key)
    {
        $key = self::getKey(func_get_args());
        $class = get_called_class();
        if (isset(self::$instances[$class][$key])) {
            return self::$instances[$class][$key];
        }
        return null;
    }

    /**
     * Returns the instance for the given key
     *
     * If the instance does not exist it will be created by calling the $createCallback
     *
     * @param callable $createCallback Callback, will be called to create a new instance
     * @param mixed    $key            Key
     * @param mixed    $key,...        Unlimited optional number of additional keys
     * @return null|static
     */
    private static function getInstanceLazy(callable $createCallback, $key)
    {
        $args = array_slice(func_get_args(), 1);
        $key = self::getKey($args);
        $class = get_called_class();
        if (!isset(self::$instances[$class][$key])) {
            $instance = call_user_func_array($createCallback, $args);
            if ($instance) {
                self::$instances[$class][$key] = $instance;
            }
        }
        if (isset(self::$instances[$class][$key])) {
            return self::$instances[$class][$key];
        }
        return null;
    }

    /**
     * Removes the instance of the given key
     *
     * @param mixed $key     Key
     * @param mixed $key,... Unlimited optional number of additional keys
     */
    public static function removeInstance($key)
    {
        unset(self::$instances[get_called_class()][self::getKey(func_get_args())]);
    }

    /**
     * Clears the instance pool
     */
    public static function clearInstancePool()
    {
        $calledClass = get_called_class();
        foreach (self::$instances as $class => $_) {
            if ($class === $calledClass || is_subclass_of($class, $calledClass)) {
                unset(self::$instances[$class]);
            }
        }
    }

    /**
     * @param array $parts
     * @return string
     */
    private static function getKey(array $parts)
    {
        return implode('###', $parts);
    }
}
