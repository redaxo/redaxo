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
   * Returns the package (addon or plugin) by the given package representation
   *
   * @param string|array $package Package representation
   *
   * @return rex_addon|rex_plugin
   */
  static public function get($package)
  {
    if(!is_string($package) && (!is_array($package) || !isset($package[0]) || !isset($package[1])))
    {
      throw new rexException('Expecting $package to be string or array with two elements!');
    }
    return is_string($package) ? rex_addon::get($package) : rex_addon::get($package[0])->getPlugin($package[1]);
  }

  /**
   * Returns if the package exists
   *
   * @param string $package
   *
   * @return boolean
   */
  static public function exists($package)
  {
    return false;
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
   * Returns the package representation
   *
   * @return string|array
   */
  abstract public function getPackageRepresentation();

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
   * Returns the config namespace
   *
   * @return string
   */
  abstract public function getConfigNamespace();

  /**
   * @see rex_config::set()
   */
  public function setConfig($key, $value)
  {
    return rex_config::set($this->getConfigNamespace(), $key, $value);
  }

  /**
   * @see rex_config::get()
   */
  public function getConfig($key, $default)
  {
    return rex_config::get($this->getConfigNamespace(), $key, $default);
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
    return in_array($this->getPackageRepresentation(), $REX['SYSTEM_PACKAGES']);
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