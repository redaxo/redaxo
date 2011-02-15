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
  private static $initialized = false;

  /**
   * Flag which indicates if database needs an update, because settings have changed.
   * @var boolean
   */
  private static $changed = false;

  /**
   * data read from database
   * @var array
   */
  private static $data = array();

  /**
   * data which is modified during this request
   * @var array
   */
  private static $changedData = array();

  /**
   * data which was deleted durint this request
   * @var array
   */
  private static $deletedData = array();

  /**
   * Method which saves an arbitary value associated to the given key.
   * The value may also be saved into a separate $namespace which preserves it from beeing overriden.
   *
   * The set-methode returns TRUE when an existing value was overridden, otherwise FALSE is returned.
   *
   * @param string $namespace A namespace e.g. an addon name
   * @param string $key The associated key
   * @param mixed $value The value to save
   *
   * @return boolean TRUE when an existing value was overridden, otherwise FALSE
   *
   * @throws rexException on invalid parameters
   */
  public static function set($namespace, $key, $value)
  {
    self::init();

    if(!is_string($namespace))
    {
      throw new rexException('rex_config: expecting $namespace to be a string');
    }
    if(!is_string($key))
    {
      throw new rexException('rex_config: expecting $key to be a string');
    }

    if(!isset(self::$data[$namespace]))
      self::$data[$namespace] = array();

    $existed = isset(self::$data[$namespace][$key]);
    if(!$existed || $existed && self::$data[$namespace][$key] != $value)
    {
      // keep track of changed data
      if(!isset(self::$changedData[$namespace]))
        self::$changedData[$namespace] = array();
      self::$changedData[$namespace][$key] = $value;

      // since it was re-added, do not longer mark as deleted
      if(isset(self::$deletedData[$namespace]) && isset(self::$deletedData[$namespace][$key]))
        unset(self::$deletedData[$namespace][$key]);

      // re-set the data in the container
      self::$data[$namespace][$key] = $value;
      self::$changed = true;
    }

    return $existed;
  }

  /**
   * Method which returns an associated value for the given key.
   * The key might also be associated to a given namespace.
   *
   * If no value can be found for the given key/namespace combination $default is returned.
   *
   * @param string $namespace A namespace e.g. an addon name
   * @param string $key The associated key
   * @param mixed $default Default return value if no associated-value can be found
   *
   * @return the value for $key or $default if $key cannot be found in the given $namespace
   *
   * @throws rexException on invalid parameters
   */
  public static function get($namespace, $key, $default = null)
  {
    self::init();

    if(!is_string($namespace))
    {
      throw new rexException('rex_config: expecting $namespace to be a string');
    }
    if(!is_string($key))
    {
      throw new rexException('rex_config: expecting $key to be a string');
    }

    if(isset(self::$data[$namespace]) && isset(self::$data[$namespace][$key]))
    {
      return self::$data[$namespace][$key];
    }
    return $default;
  }

  /**
   * Returns if the given key is set.
   *
   * @param string $namespace A namespace e.g. an addon name
   * @param string $key The associated key
   *
   * @return boolean TRUE if the key is set, otherwise FALSE
   *
   * @throws rexException on invalid parameters
   */
  public static function has($namespace, $key)
  {
    self::init();

    if(!is_string($namespace))
    {
      throw new rexException('rex_config: expecting $namespace to be a string');
    }
    if(!is_string($key))
    {
      throw new rexException('rex_config: expecting $key to be a string');
    }

    return isset(self::$data[$namespace][$key]);
  }

  /**
   * Removes the setting associated with the given key.
   * The key might also be associated to a given namespace.
   *
   * @param string $namespace A namespace e.g. an addon name
   * @param string $key The associated key
   *
   * @return boolean TRUE if the value was found and removed, otherwise FALSE
   *
   * @throws rexException on invalid parameters
   */
  public static function remove($namespace, $key)
  {
    self::init();

    if(!is_string($namespace))
    {
      throw new rexException('rex_config: expecting $namespace to be a string');
    }
    if(!is_string($key))
    {
      throw new rexException('rex_config: expecting $key to be a string');
    }

    if(isset(self::$data[$namespace]) && isset(self::$data[$namespace][$key]))
    {
      // keep track of deleted data
      if(!isset(self::$deletedData[$namespace]))
        self::$deletedData[$namespace] = array();
      self::$deletedData[$namespace][$key] = true;

      // since it will be deleted, do not longer mark as changed
      if(isset(self::$changedData[$namespace]) && isset(self::$changedData[$namespace][$key]))
        unset(self::$changedData[$namespace][$key]);

      // delete the data from the container
      unset(self::$data[$namespace][$key]);
      self::$changed = true;
      return true;
    }
    return false;
  }

  /**
   * Removes all settings associated with the given namespace
   *
   * @param string $namespace A namespace e.g. an addon name
   *
   * @return TRUE if the namespace was found and removed, otherwise FALSE
   *
   * @throws rexException
   */
  public static function removeNamespace($namespace)
  {
    self::init();

    if(!is_string($namespace))
    {
      throw new rexException('rex_config: expecting $namespace to be a string');
    }

    if(isset(self::$data[$namespace]))
    {
      foreach(self::$data[$namespace] as $key => $value)
      {
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
  protected static function init()
  {
    global $REX;

    if(self::$initialized)
      return;

    define('REX_CONFIG_FILE_CACHE', rex_path::generated('files/config.cache'));

    // take care, so we are able to write a cache file on shutdown
    // (check here, since exceptions in shutdown functions are not visible to the user)
    if(!is_writable(dirname(REX_CONFIG_FILE_CACHE)))
    {
      throw new rexException('rex-config: cache dir "'. dirname(REX_CONFIG_FILE_CACHE) .'" is not writable!');
    }

    // save cache on shutdown
    register_shutdown_function(array(__CLASS__, 'save'));

    self::load();
    self::$initialized = true;
  }

  /**
   * load the config-data
   */
  protected static function load()
  {
    // check if we can load the config from the filesystem
    if(!self::loadFromFile())
    {
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
  private static function loadFromFile()
  {
    // delete cache-file, will be regenerated on next request
    if(file_exists(REX_CONFIG_FILE_CACHE))
    {
      self::$data = sfYaml::load(REX_CONFIG_FILE_CACHE);
      return true;
    }
    return false;
  }

  /**
   * load the config-data from database
   */
  private static function loadFromDb()
  {
    global $REX;

    $sql = rex_sql::factory();
    $sql->setQuery('SELECT * FROM '. $REX['TABLE_PREFIX']. 'config');

    self::$data = array();
    while($sql->hasNext())
    {
      self::$data[$sql->getValue('namespace')][$sql->getValue('key')] = unserialize($sql->getValue('value'));
      $sql->next();
    }
  }

  /**
   * save config to file-cache
   */
  private static function generateCache()
  {
    if(rex_put_file_contents(REX_CONFIG_FILE_CACHE, sfYaml::dump(self::$data)) <= 0)
    {
      throw new rexException('rex-config: unable to write cache file '. REX_CONFIG_FILE_CACHE);
    }
  }

  /**
   * persists the config-data and truncates the file-cache
   */
  public static function save()
  {
    // save cache only if changes happened
    if(!self::$changed)
      return;

    // after all no data needs to be deleted or update, so skip save
    if(empty(self::$deletedData) && empty(self::$changedData))
      return;

    // delete cache-file; will be regenerated on next request
    if(file_exists(REX_CONFIG_FILE_CACHE))
    {
      unlink(REX_CONFIG_FILE_CACHE);
    }

    // save all data to the db
    self::saveToDb();
    self::$changed = false;
  }

  /**
   * save the config-data into the db
   */
  private static function saveToDb()
  {
    global $REX;

    $sql = rex_sql::factory();
    // $sql->debugsql = true;

    // remove all deleted data
    foreach(self::$deletedData as $namespace => $nsData)
    {
      foreach($nsData as $key => $value)
      {
        $sql->setTable($REX['TABLE_PREFIX']. 'config');
        $sql->setWhere('namespace="'. $namespace .'" and `key`="'. $key .'"');
        $sql->delete();
      }
    }

    // update all changed data
    foreach(self::$changedData as $namespace => $nsData)
    {
      foreach($nsData as $key => $value)
      {
        $sql->setTable($REX['TABLE_PREFIX']. 'config');
        $sql->setValue('namespace', $namespace);
        $sql->setValue('key', $key);
        $sql->setValue('value', serialize($value));
        $sql->replace();
      }
    }
  }
}