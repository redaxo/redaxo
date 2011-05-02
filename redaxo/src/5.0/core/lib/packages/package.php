<?php

/**
 * Abstract base class for packages
 *
 * @author gharlan
 */
abstract class rex_package
{
  /**
   * Name of the package
   *
   * @var string
   */
  private $name;

  /**
   * Properties
   *
   * @var array
   */
  private $properties = array();

  /**
   * Constructor
   *
   * @param string $name Name
   */
  public function __construct($name)
  {
    $this->name = $name;
  }

  /**
   * Returns the package (addon or plugin) by the given package id
   *
   * @param string $packageId Package ID
   *
   * @return rex_addon|rex_plugin
   */
  static public function get($packageId)
  {
    if(!is_string($packageId))
    {
      throw new rexException('Expecting $packageId to be string, but '. gettype($packageId) .' given!');
    }
    $package = explode('/', $packageId);
    $addon = rex_addon::get($package[0]);
    if(isset($package[1]))
    {
      return $addon->getPlugin($package[1]);
    }
    return $addon;
  }

  /**
   * Returns if the package exists
   *
   * @param string $packageId Package ID
   *
   * @return boolean
   */
  static public function exists($packageId)
  {
    $package = explode('/', $packageId);
    if(isset($package[1]))
    {
      return rex_plugin::exists($package[0], $package[1]);
    }
    return rex_addon::exists($package[0]);
  }

  /**
   * Returns the name of the package
   *
   * @return string Name
   */
  public function getName()
  {
    return $this->name;
  }

  /**
   * Returns the related Addon
   */
  abstract public function getAddon();

  /**
   * Returns the package ID
   *
   * @return string
   */
  abstract public function getPackageId();

  /**
   * Returns the base path
   *
   * @param string $file File
   */
  abstract public function getBasePath($file = '');

  /**
   * Returns the assets path
   *
   * @param string $file File
   */
  abstract public function getAssetsPath($file = '');

  /**
   * Returns the data path
   *
   * @param string $file File
   */
  abstract public function getDataPath($file = '');

  /**
   * @see rex_config::set()
   */
  public function setConfig($key, $value)
  {
    return rex_config::set($this->getPackageId(), $key, $value);
  }

  /**
   * @see rex_config::get()
   */
  public function getConfig($key, $default)
  {
    return rex_config::get($this->getPackageId(), $key, $default);
  }

  /**
   * @see rex_config::has()
   */
  public function hasConfig($key)
  {
    return rex_config::has($this->getPackageId(), $key);
  }

  /**
   * Sets a property
   *
   * @param string $key Key of the property
   * @param mixed $value New value for the property
   */
  public function setProperty($key, $value)
  {
    if(!is_string($key))
    {
      throw new rexException('Expecting $key to be string, but '. gettype($key) .' given!');
    }
    $this->properties[$key] = $value;
  }

  /**
   * Returns a property
   *
   * @param string $key Key of the property
   * @param mixed $default Default value, will be returned if the property isn't set
   *
   * @return mixed
   */
  public function getProperty($key, $default = null)
  {
    if(!is_string($key))
    {
      throw new rexException('Expecting $key to be string, but '. gettype($key) .' given!');
    }
    if(isset($this->properties[$key]))
    {
      return $this->properties[$key];
    }
    return $default;
  }

  /**
   * Returns if a property is set
   *
   * @param string $key Key of the property
   *
   * @return boolean
   */
  public function hasProperty($key)
  {
    return is_string($key) && isset($this->properties[$key]);
  }

	/**
   * Returns if the package is available (activated and installed)
   *
   * @return boolean
   */
  public function isAvailable()
  {
    return $this->isInstalled() && $this->isActivated();
  }

	/**
   * Returns if the package is installed
   *
   * @return boolean
   */
  public function isInstalled()
  {
    return (boolean) $this->getProperty('install', false);
  }

	/**
   * Returns if the package is activated
   *
   * @return boolean
   */
  public function isActivated()
  {
    return (boolean) $this->getProperty('status', false);
  }

	/**
   * Returns if it is a system package
   *
   * @return boolean
   */
  public function isSystemPackage()
  {
    global $REX;
    return in_array($this->getPackageId(), $REX['SYSTEM_PACKAGES']);
  }

  /**
   * Returns the author
   *
   * @param mixed $default Default value, will be returned if the property isn't set
   *
   * @return mixed
   */
  public function getAuthor($default = null)
  {
    return $this->getProperty('author', $default);
  }

  /**
   * Returns the version
   *
   * @param mixed $default Default value, will be returned if the property isn't set
   *
   * @return mixed
   */
  public function getVersion($default = null)
  {
    return $this->getProperty('version', $default);
  }

  /**
   * Returns the supportpage
   *
   * @param mixed $default Default value, will be returned if the property isn't set
   *
   * @return mixed
   */
  public function getSupportPage($default = null)
  {
    return $this->getProperty('supportpage', $default);
  }

  /**
   * Includes a file in the package context
   *
   * @param string $file Filename
   */
  public function includeFile($file)
  {
    global $REX;
    include $this->getBasePath($file);
  }
}


/**
 * Represents a dummy package that doesn't exists in file system
 *
 * @author gharlan
 */
interface rex_nullPackage
{
  /**
   * Returns the singleton instance
   *
   * @return rex_nullPackage
   */
  static public function getInstance();
}