<?php

/**
 * REX base class for core properties etc.
 *
 * @author gharlan
 */
class rex
{
  const CONFIG_NAMESPACE = 'rex-core';

  /**
   * Array of properties
   *
   * @var array
   */
  static protected $properties = array();

  /**
   * @see rex_config::set()
   */
  static public function setConfig($key, $value)
  {
    return rex_config::set(self::CONFIG_NAMESPACE, $key, $value);
  }

  /**
   * @see rex_config::get()
   */
  static public function getConfig($key, $default = null)
  {
    return rex_config::get(self::CONFIG_NAMESPACE, $key, $default);
  }

  /**
   * @see rex_config::has()
   */
  static public function hasConfig($key)
  {
    return rex_config::has(self::CONFIG_NAMESPACE, $key);
  }

  /**
   * @see rex_config::remove()
   */
  static public function removeConfig($key)
  {
    return rex_config::remove(self::CONFIG_NAMESPACE, $key);
  }

  /**
   * Sets a property
   *
   * @param string $key Key of the property
   * @param mixed $value Value for the property
   *
   * @return boolean TRUE when an existing value was overridden, otherwise FALSE
   *
   * @throws rex_exception on invalid parameters
   */
  static public function setProperty($key, $value)
  {
    if (!is_string($key)) {
      throw new rex_exception('Expecting $key to be string, but ' . gettype($key) . ' given!');
    }
    $exists = isset(self::$properties[$key]);
    self::$properties[$key] = $value;
    return $exists;
  }

  /**
   * Returns a property
   *
   * @param string $key Key of the property
   * @param mixed $default Default value, will be returned if the property isn't set
   *
   * @return the value for $key or $default if $key cannot be found
   *
   * @throws rex_exception on invalid parameters
   */
  static public function getProperty($key, $default = null)
  {
    if (!is_string($key)) {
      throw new rex_exception('Expecting $key to be string, but ' . gettype($key) . ' given!');
    }
    if (isset(self::$properties[$key])) {
      return self::$properties[$key];
    }
    return $default;
  }

  /**
   * Returns if a property is set
   *
   * @param string $key Key of the property
   *
   * @return boolean TRUE if the key is set, otherwise FALSE
   *
   * @throws rex_exception on invalid parameters
   */
  static public function hasProperty($key)
  {
    return is_string($key) && isset(self::$properties[$key]);
  }

  /**
   * Removes a property
   *
   * @param string $key Key of the property
   *
   * @return boolean TRUE if the value was found and removed, otherwise FALSE
   *
   * @throws rex_exception on invalid parameters
   */
  static public function removeProperty($key)
  {
    if (!is_string($key)) {
      throw new rex_exception('Expecting $key to be string, but ' . gettype($key) . ' given!');
    }
    $exists = isset(self::$properties[$key]);
    unset(self::$properties[$key]);
    return $exists;
  }

  /**
   * Returns if the setup is active
   *
   * @return boolean
   */
  static public function isSetup()
  {
    return (boolean) self::getProperty('setup', false);
  }

  /**
   * Returns if the environment is the backend
   *
   * @return boolean
   */
  static public function isBackend()
  {
    return (boolean) self::getProperty('redaxo', false);
  }

  /**
   * Returns if the safe mode is active
   *
   * @return boolean
   */
  static public function isSafeMode()
  {
    return self::isBackend() && rex_session('safemode', 'boolean', false);
  }

  /**
   * Returns the table prefix
   *
   * @return string
   */
  static public function getTablePrefix()
  {
    return self::getProperty('table_prefix');
  }

  /**
   * Adds the table prefix to the table name
   *
   * @param string $table Table name
   * @return string
   */
  static public function getTable($table)
  {
    return self::getTablePrefix() . $table;
  }

  /**
   * Returns the temp prefix
   *
   * @return string
   */
  static public function getTempPrefix()
  {
    return self::getProperty('temp_prefix');
  }

  /**
   * Returns the current user
   *
   * @return rex_user
   */
  static public function getUser()
  {
    return self::getProperty('user');
  }

  /**
   * Returns the redaxo version
   *
   * @param string $separator Separator between version, subversion and minorversion
   * @return string
   */
  static public function getVersion($separator = '.')
  {
    return self::getProperty('version') . $separator . self::getProperty('subversion') . $separator . self::getProperty('minorversion');
  }

  /**
   * Returns the title tag and if the property "use_accesskeys" is true, the accesskey tag
   *
   * @param string $title Title
   * @param string $key Key for the accesskey
   * @return string
   */
  static public function getAccesskey($title, $key)
  {
    if (self::getProperty('use_accesskeys')) {
      $accesskeys = (array) self::getProperty('accesskeys', array());
      if (isset($accesskeys[$key]))
        return ' accesskey="' . $accesskeys[$key] . '" title="' . $title . ' [' . $accesskeys[$key] . ']"';
    }

    return ' title="' . $title . '"';
  }

  /**
   * Returns the file perm
   *
   * @return int
   */
  static public function getFilePerm()
  {
    return (int) rex::getProperty('fileperm', 0664);
  }

  /**
   * Returns the dir perm
   *
   * @return int
   */
  static public function getDirPerm()
  {
    return (int) rex::getProperty('dirperm', 0775);
  }
}
