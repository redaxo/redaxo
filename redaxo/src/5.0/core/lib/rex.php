<?php

class rex
{
  const CONFIG_NAMESPACE = 'rex-core';

  static private $properties = array();

  static public function setConfig($key, $value)
  {
    return rex_config::set(self::CONFIG_NAMESPACE, $key, $value);
  }

  static public function getConfig($key, $default = null)
  {
    return rex_config::get(self::CONFIG_NAMESPACE, $key, $default);
  }

  static public function hasConfig($key)
  {
    return rex_config::has(self::CONFIG_NAMESPACE, $key);
  }

  static public function setProperty($key, $value)
  {
    if(!is_string($key))
    {
      throw new rexException('Expecting $key to be string, but '. gettype($key) .' given!');
    }
    self::$properties[$key] = $value;
  }

  static public function getProperty($key, $default = null)
  {
    if(!is_string($key))
    {
      throw new rexException('Expecting $key to be string, but '. gettype($key) .' given!');
    }
    if(isset(self::$properties[$key]))
    {
      return self::$properties[$key];
    }
    return $default;
  }

  static public function hasProperty($key)
  {
    return is_string($key) && isset(self::$properties[$key]);
  }

  static public function isSetup()
  {
    return (boolean) self::getProperty('setup', false);
  }

  static public function isBackend()
  {
    return (boolean) self::getProperty('redaxo', false);
  }

  static public function getTablePrefix()
  {
    return self::getProperty('table_prefix');
  }

  static public function getTempPrefix()
  {
    return self::getProperty('temp_prefix');
  }

  static public function getUser()
  {
    return self::getProperty('user');
  }

  static public function getVersion($separator = '.')
  {
    return self::getProperty('version') . $separator . self::getProperty('subversion') . $separator . self::getProperty('minorversion');
  }

  static public function getAccesskey($title, $key)
  {
    if(self::getUser()->hasPerm('accesskeys[]'))
    {
      $accesskeys = (array) self::getProperty('accesskeys', array());
      if(isset($accesskeys[$key]))
        return ' accesskey="'. $accesskeys[$key] .'" title="'. $title .' ['. $accesskeys[$key] .']"';
    }

    return ' title="'. $title .'"';
  }
}