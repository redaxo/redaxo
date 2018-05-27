<?php

/**
 * Class for handling configurations.
 * The configuration is persisted between requests.
 *
 * @author staabm
 *
 * @package redaxo\core
 */
class rex_config
{
    /**
     * Flag to indicate if the config was initialized.
     *
     * @var bool
     */
    private static $initialized = false;

    /**
     * Flag which indicates if database needs an update, because settings have changed.
     *
     * @var bool
     */
    private static $changed = false;

    /**
     * data read from database.
     *
     * @var array
     */
    private static $data = [];

    /**
     * data which is modified during this request.
     *
     * @var array
     */
    private static $changedData = [];

    /**
     * data which was deleted during this request.
     *
     * @var array
     */
    private static $deletedData = [];

    /**
     * Method which saves an arbitary value associated to the given namespace and key.
     * If the second parameter is an associative array, all key/value pairs will be saved.
     *
     * The set-method returns TRUE when an existing value was overridden, otherwise FALSE is returned.
     *
     * @param string       $namespace The namespace e.g. an addon name
     * @param string|array $key       The associated key or an associative array of key/value pairs
     * @param mixed        $value     The value to save
     *
     * @throws InvalidArgumentException
     *
     * @return bool TRUE when an existing value was overridden, otherwise FALSE
     */
    public static function set($namespace, $key, $value = null)
    {
        self::init();

        if (!is_string($namespace)) {
            throw new InvalidArgumentException('rex_config: expecting $namespace to be a string, ' . gettype($namespace) . ' given!');
        }

        if (is_array($key)) {
            $existed = false;
            foreach ($key as $k => $v) {
                $existed = self::set($namespace, $k, $v) || $existed;
            }
            return $existed;
        }

        if (!is_string($key)) {
            throw new InvalidArgumentException('rex_config: expecting $key to be a string, ' . gettype($key) . ' given!');
        }

        if (!isset(self::$data[$namespace])) {
            self::$data[$namespace] = [];
        }

        $existed = isset(self::$data[$namespace][$key]);
        if (!$existed || $existed && self::$data[$namespace][$key] !== $value) {
            // keep track of changed data
            self::$changedData[$namespace][$key] = $value;

            // since it was re-added, do not longer mark as deleted
            unset(self::$deletedData[$namespace][$key]);

            // re-set the data in the container
            self::$data[$namespace][$key] = $value;
            self::$changed = true;
        }

        return $existed;
    }

    /**
     * Method which returns an associated value for the given namespace and key.
     * If $key is null, an array of all key/value pairs for the given namespace will be returned.
     *
     * If no value can be found for the given key/namespace combination $default is returned.
     *
     * @param string $namespace The namespace e.g. an addon name
     * @param string $key       The associated key
     * @param mixed  $default   Default return value if no associated-value can be found
     *
     * @throws InvalidArgumentException
     *
     * @return mixed the value for $key or $default if $key cannot be found in the given $namespace
     */
    public static function get($namespace, $key = null, $default = null)
    {
        self::init();

        if (!is_string($namespace)) {
            throw new InvalidArgumentException('rex_config: expecting $namespace to be a string, ' . gettype($namespace) . ' given!');
        }

        if ($key === null) {
            return isset(self::$data[$namespace]) ? self::$data[$namespace] : [];
        }

        if (!is_string($key)) {
            throw new InvalidArgumentException('rex_config: expecting $key to be a string, ' . gettype($key) . ' given!');
        }

        if (isset(self::$data[$namespace][$key])) {
            return self::$data[$namespace][$key];
        }
        return $default;
    }

    /**
     * Returns if the given key is set.
     *
     * @param string $namespace The namespace e.g. an addon name
     * @param string $key       The associated key
     *
     * @throws InvalidArgumentException
     *
     * @return bool TRUE if the key is set, otherwise FALSE
     */
    public static function has($namespace, $key = null)
    {
        self::init();

        if (!is_string($namespace)) {
            throw new InvalidArgumentException('rex_config: expecting $namespace to be a string, ' . gettype($namespace) . ' given!');
        }

        if ($key === null) {
            return isset(self::$data[$namespace]);
        }

        if (!is_string($key)) {
            throw new InvalidArgumentException('rex_config: expecting $key to be a string, ' . gettype($key) . ' given!');
        }

        return isset(self::$data[$namespace][$key]);
    }

    /**
     * Removes the setting associated with the given namespace and key.
     *
     * @param string $namespace The namespace e.g. an addon name
     * @param string $key       The associated key
     *
     * @throws InvalidArgumentException
     *
     * @return bool TRUE if the value was found and removed, otherwise FALSE
     */
    public static function remove($namespace, $key)
    {
        self::init();

        if (!is_string($namespace)) {
            throw new InvalidArgumentException('rex_config: expecting $namespace to be a string, ' . gettype($namespace) . ' given!');
        }
        if (!is_string($key)) {
            throw new InvalidArgumentException('rex_config: expecting $key to be a string, ' . gettype($key) . ' given!');
        }

        if (isset(self::$data[$namespace][$key])) {
            // keep track of deleted data
            self::$deletedData[$namespace][$key] = true;

            // since it will be deleted, do not longer mark as changed
            unset(self::$changedData[$namespace][$key]);

            // delete the data from the container
            unset(self::$data[$namespace][$key]);
            if (empty(self::$data[$namespace])) {
                unset(self::$data[$namespace]);
            }
            self::$changed = true;
            return true;
        }
        return false;
    }

    /**
     * Removes all settings associated with the given namespace.
     *
     * @param string $namespace The namespace e.g. an addon name
     *
     * @throws InvalidArgumentException
     *
     * @return bool TRUE if the namespace was found and removed, otherwise FALSE
     */
    public static function removeNamespace($namespace)
    {
        self::init();

        if (!is_string($namespace)) {
            throw new InvalidArgumentException('rex_config: expecting $namespace to be a string, ' . gettype($namespace) . ' given!');
        }

        if (isset(self::$data[$namespace])) {
            foreach (self::$data[$namespace] as $key => $value) {
                self::remove($namespace, $key);
            }

            unset(self::$data[$namespace]);
            self::$changed = true;
            return true;
        }
        return false;
    }

    /**
     * Refreshes rex_config by reloading config from db.
     */
    public static function refresh()
    {
        if (!self::$initialized) {
            self::init();

            return;
        }

        self::loadFromDb();

        self::generateCache();

        self::$changed = false;
        self::$changedData = [];
        self::$deletedData = [];
    }

    /**
     * initilizes the rex_config class.
     */
    protected static function init()
    {
        if (self::$initialized) {
            return;
        }

        define('REX_CONFIG_FILE_CACHE', rex_path::coreCache('config.cache'));

        // take care, so we are able to write a cache file on shutdown
        // (check here, since exceptions in shutdown functions are not visible to the user)
        $dir = dirname(REX_CONFIG_FILE_CACHE);
        rex_dir::create($dir);
        if (!is_writable($dir)) {
            throw new rex_exception('rex-config: cache dir "' . dirname(REX_CONFIG_FILE_CACHE) . '" is not writable!');
        }

        // save cache on shutdown
        register_shutdown_function([__CLASS__, 'save']);

        self::load();
        self::$initialized = true;
    }

    /**
     * load the config-data.
     */
    protected static function load()
    {
        // check if we can load the config from the filesystem
        if (!self::loadFromFile()) {
            // if not possible, fallback to load config from the db
            self::loadFromDb();
            // afterwards persist loaded data into file-cache
            self::generateCache();
        }
    }

    /**
     * load the config-data from a file-cache.
     *
     * @return bool Returns TRUE, if the data was successfully loaded from the file-cache, otherwise FALSE
     */
    private static function loadFromFile()
    {
        // delete cache-file, will be regenerated on next request
        if (file_exists(REX_CONFIG_FILE_CACHE)) {
            self::$data = rex_file::getCache(REX_CONFIG_FILE_CACHE);
            return true;
        }
        return false;
    }

    /**
     * load the config-data from database.
     */
    private static function loadFromDb()
    {
        $sql = rex_sql::factory();
        $sql->setQuery('SELECT * FROM ' . rex::getTablePrefix() . 'config');

        self::$data = [];
        foreach ($sql as $cfg) {
            self::$data[$cfg->getValue('namespace')][$cfg->getValue('key')] = json_decode($cfg->getValue('value'), true);
        }
    }

    /**
     * save config to file-cache.
     */
    private static function generateCache()
    {
        if (rex_file::putCache(REX_CONFIG_FILE_CACHE, self::$data) <= 0) {
            throw new rex_exception('rex-config: unable to write cache file ' . REX_CONFIG_FILE_CACHE);
        }
    }

    /**
     * persists the config-data and truncates the file-cache.
     */
    public static function save()
    {
        // save cache only if changes happened
        if (!self::$changed) {
            return;
        }

        // after all no data needs to be deleted or update, so skip save
        if (empty(self::$deletedData) && empty(self::$changedData)) {
            return;
        }

        // delete cache-file; will be regenerated on next request
        rex_file::delete(REX_CONFIG_FILE_CACHE);

        // save all data to the db
        self::saveToDb();
        self::$changed = false;
        self::$changedData = [];
        self::$deletedData = [];
    }

    /**
     * save the config-data into the db.
     */
    private static function saveToDb()
    {
        $sql = rex_sql::factory();
        // $sql->setDebug();

        // remove all deleted data
        if (self::$deletedData) {
            $sql->setTable(rex::getTable('config'));

            $where = [];
            $params = [];
            foreach (self::$deletedData as $namespace => $nsData) {
                $params = array_merge($params, [$namespace], array_keys($nsData));
                $where[] = 'namespace = ? AND `key` IN ('.implode(', ', array_fill(0, count($nsData), '?')).')';
            }

            $sql->setWhere(implode("\n    OR ", $where), $params);
            $sql->delete();
        }

        // update all changed data
        if (self::$changedData) {
            $sql->setTable(rex::getTable('config'));

            foreach (self::$changedData as $namespace => $nsData) {
                foreach ($nsData as $key => $value) {
                    $sql->addRecord(function (rex_sql $record) use ($namespace, $key, $value) {
                        $record->setValue('namespace', $namespace);
                        $record->setValue('key', $key);
                        $record->setValue('value', json_encode($value));
                    });
                }
            }

            $sql->insertOrUpdate();
        }
    }
}
