<?php

/**
 * Instance List Pool Trait.
 *
 * @author gharlan
 *
 * @package redaxo\core
 */
trait rex_instance_list_pool_trait
{
    /**
     * @var mixed[]
     */
    private static $instanceLists = [];

    /**
     * Adds an instance list.
     *
     * @param mixed $key             Key
     * @param array $instanceKeyList Array of instance keys
     */
    protected static function addInstanceList($key, array $instanceKeyList)
    {
        $key = self::getInstanceListPoolKey($key);
        self::$instanceLists[$key] = $instanceKeyList;
    }

    /**
     * Checks whether an instance list exists for the given key.
     *
     * @param mixed $key Key
     *
     * @return bool
     */
    protected static function hasInstanceList($key)
    {
        $key = self::getInstanceListPoolKey($key);
        return isset(self::$instanceLists[$key]);
    }

    /**
     * Returns the instance list for the given key.
     *
     * If the instance list does not exist it will be created by calling the $createListCallback
     *
     * @param mixed    $key                 Key
     * @param callable $getInstanceCallback Callback, will be called for every list item to get the instance
     * @param callable $createListCallback  Callback, will be called to create the list of instance keys
     *
     * @return array
     */
    protected static function getInstanceList($key, callable $getInstanceCallback, callable $createListCallback = null)
    {
        $args = (array) $key;
        $key = self::getInstanceListPoolKey($args);
        if (!isset(self::$instanceLists[$key]) && $createListCallback) {
            $list = call_user_func_array($createListCallback, $args);
            self::$instanceLists[$key] = is_array($list) ? $list : [];
        }
        if (!isset(self::$instanceLists[$key])) {
            return [];
        }
        $list = [];
        foreach (self::$instanceLists[$key] as $instanceKey) {
            $instance = call_user_func_array($getInstanceCallback, (array) $instanceKey);
            if ($instance) {
                $list[] = $instance;
            }
        }
        return $list;
    }

    /**
     * Clears the instance list of the given key.
     *
     * @param mixed $key Key
     */
    public static function clearInstanceList($key)
    {
        $key = self::getInstanceListPoolKey($key);
        unset(self::$instanceLists[$key]);
    }

    /**
     * Clears the instance list pool.
     */
    public static function clearInstanceListPool()
    {
        self::$instanceLists = [];
    }

    /**
     * Returns a string representation for the key.
     *
     * The original key can be a scalar value or an array of scalar values
     *
     * @param mixed $key Key
     *
     * @return string
     */
    private static function getInstanceListPoolKey($key)
    {
        return implode('###', (array) $key);
    }
}
