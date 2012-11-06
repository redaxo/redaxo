<?php

/**
 * Class for handling configurations.
 * The configuration is persisted between requests.
 *
 * @author staabm
 */
class rex_config
{
  /**
   * Flag to indicate if the config was initialized
   * @var boolean
   */
  static private $initialized = false;

  /**
   * Flag which indicates if database needs an update, because settings have changed.
   * @var boolean
   */
  static private $changed = false;

  /**
   * data read from database
   * @var array
   */
  static private $data = array();

  /**
   * data which is modified during this request
   * @var array
   */
  static private $changedData = array();

  /**
   * data which was deleted during this request
   * @var array
   */
  static private $deletedData = array();

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
   * @return boolean TRUE when an existing value was overridden, otherwise FALSE
   *
   * @throws rex_exception on invalid parameters
   */
  static public function set($namespace, $key, $value = null)
  {
    self::init();

    if (!is_string($namespace)) {
      throw new rex_exception('rex_config: expecting $namespace to be a string, ' . gettype($namespace) . ' given!');
    }

    if (is_array($key)) {
      $existed = false;
      foreach ($key as $k => $v) {
        $existed = self::set($namespace, $k, $v) || $existed;
      }
      return $existed;
    }

    if (!is_string($key)) {
      throw new rex_exception('rex_config: expecting $key to be a string, ' . gettype($key) . ' given!');
    }

    if (!isset(self::$data[$namespace]))
      self::$data[$namespace] = array();

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
   * @return the value for $key or $default if $key cannot be found in the given $namespace
   *
   * @throws rex_exception on invalid parameters
   */
  static public function get($namespace, $key = null, $default = null)
  {
    self::init();

    if (!is_string($namespace)) {
      throw new rex_exception('rex_config: expecting $namespace to be a string, ' . gettype($namespace) . ' given!');
    }

    if ($key === null) {
      return isset(self::$data[$namespace]) ? self::$data[$namespace] : array();
    }

    if (!is_string($key)) {
      throw new rex_exception('rex_config: expecting $key to be a string, ' . gettype($key) . ' given!');
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
   * @return boolean TRUE if the key is set, otherwise FALSE
   *
   * @throws rex_exception on invalid parameters
   */
  static public function has($namespace, $key = null)
  {
    self::init();

    if (!is_string($namespace)) {
      throw new rex_exception('rex_config: expecting $namespace to be a string, ' . gettype($namespace) . ' given!');
    }

    if ($key === null) {
      return isset(self::$data[$namespace]);
    }

    if (!is_string($key)) {
      throw new rex_exception('rex_config: expecting $key to be a string, ' . gettype($key) . ' given!');
    }

    return isset(self::$data[$namespace][$key]);
  }

  /**
   * Removes the setting associated with the given namespace and key.
   *
   * @param string $namespace The namespace e.g. an addon name
   * @param string $key       The associated key
   *
   * @return boolean TRUE if the value was found and removed, otherwise FALSE
   *
   * @throws rex_exception on invalid parameters
   */
  static public function remove($namespace, $key)
  {
    self::init();

    if (!is_string($namespace)) {
      throw new rex_exception('rex_config: expecting $namespace to be a string, ' . gettype($namespace) . ' given!');
    }
    if (!is_string($key)) {
      throw new rex_exception('rex_config: expecting $key to be a string, ' . gettype($key) . ' given!');
    }

    if (isset(self::$data[$namespace][$key])) {
      // keep track of deleted data
      self::$deletedData[$namespace][$key] = true;

      // since it will be deleted, do not longer mark as changed
      unset(self::$changedData[$namespace][$key]);

      // delete the data from the container
      unset(self::$data[$namespace][$key]);
      if (empty(self::$data[$namespace]))
        unset(self::$data[$namespace]);
      self::$changed = true;
      return true;
    }
    return false;
  }

  /**
   * Removes all settings associated with the given namespace
   *
   * @param string $namespace The namespace e.g. an addon name
   *
   * @return TRUE if the namespace was found and removed, otherwise FALSE
   *
   * @throws rex_exception
   */
  static public function removeNamespace($namespace)
  {
    self::init();

    if (!is_string($namespace)) {
      throw new rex_exception('rex_config: expecting $namespace to be a string, ' . gettype($namespace) . ' given!');
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
   * initilizes the rex_config class
   */
  static protected function init()
  {
    if (self::$initialized)
      return;

    define('REX_CONFIG_FILE_CACHE', rex_path::cache('config.cache'));

    // take care, so we are able to write a cache file on shutdown
    // (check here, since exceptions in shutdown functions are not visible to the user)
    if (!is_writable(dirname(REX_CONFIG_FILE_CACHE))) {
      throw new rex_exception('rex-config: cache dir "' . dirname(REX_CONFIG_FILE_CACHE) . '" is not writable!');
    }

    // save cache on shutdown
    register_shutdown_function(array(__CLASS__, 'save'));

    self::load();
    self::$initialized = true;
  }

  /**
   * load the config-data
   */
  static protected function load()
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
   * load the config-data from a file-cache
   *
   * @return Returns TRUE, if the data was successfully loaded from the file-cache, otherwise FALSE.
   */
  static private function loadFromFile()
  {
    // delete cache-file, will be regenerated on next request
    if (file_exists(REX_CONFIG_FILE_CACHE)) {
      self::$data = rex_file::getCache(REX_CONFIG_FILE_CACHE);
      return true;
    }
    return false;
  }

  /**
   * load the config-data from database
   */
  static private function loadFromDb()
  {
    $sql = rex_sql::factory();
    $sql->setQuery('SELECT * FROM ' . rex::getTablePrefix() . 'config');

    self::$data = array();
    foreach ($sql as $cfg) {
      self::$data[$cfg->getValue('namespace')][$cfg->getValue('key')] = json_decode($cfg->getValue('value'), true);
    }
  }

  /**
   * save config to file-cache
   */
  static private function generateCache()
  {
    if (rex_file::putCache(REX_CONFIG_FILE_CACHE, self::$data) <= 0) {
      throw new rex_exception('rex-config: unable to write cache file ' . REX_CONFIG_FILE_CACHE);
    }
  }

  /**
   * persists the config-data and truncates the file-cache
   */
  static public function save()
  {
    // save cache only if changes happened
    if (!self::$changed)
      return;

    // after all no data needs to be deleted or update, so skip save
    if (empty(self::$deletedData) && empty(self::$changedData))
      return;

    // delete cache-file; will be regenerated on next request
    rex_file::delete(REX_CONFIG_FILE_CACHE);

    // save all data to the db
    self::saveToDb();
    self::$changed = false;
  }

  /**
   * save the config-data into the db
   */
  static private function saveToDb()
  {
    $sql = rex_sql::factory();
    // $sql->debugsql = true;

    // remove all deleted data
    foreach (self::$deletedData as $namespace => $nsData) {
      foreach ($nsData as $key => $value) {
        $sql->setTable(rex::getTablePrefix() . 'config');
        $sql->setWhere(array(
          'namespace' => $namespace,
          'key' => $key
        ));
        $sql->delete();
      }
    }

    // update all changed data
    foreach (self::$changedData as $namespace => $nsData) {
      foreach ($nsData as $key => $value) {
        $sql->setTable(rex::getTablePrefix() . 'config');
        $sql->setValue('namespace', $namespace);
        $sql->setValue('key', $key);
        $sql->setValue('value', json_encode($value));
        $sql->replace();
      }
    }
  }
}
