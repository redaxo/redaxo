<?php

/**
 * Abstract base class for packages
 *
 * @author gharlan
 */
abstract class rex_package implements rex_i_package
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

  /* (non-PHPdoc)
   * @see rex_i_package::getName()
   */
  public function getName()
  {
    return $this->name;
  }

  /* (non-PHPdoc)
   * @see rex_i_package::setConfig()
   */
  public function setConfig($key, $value)
  {
    return rex_config::set($this->getPackageId(), $key, $value);
  }

  /* (non-PHPdoc)
   * @see rex_i_package::getConfig()
   */
  public function getConfig($key, $default = null)
  {
    return rex_config::get($this->getPackageId(), $key, $default);
  }

  /* (non-PHPdoc)
   * @see rex_i_package::hasConfig()
   */
  public function hasConfig($key)
  {
    return rex_config::has($this->getPackageId(), $key);
  }

  /* (non-PHPdoc)
   * @see rex_i_package::setProperty()
   */
  public function setProperty($key, $value)
  {
    if(!is_string($key))
    {
      throw new rexException('Expecting $key to be string, but '. gettype($key) .' given!');
    }
    $this->properties[$key] = $value;
  }

  /* (non-PHPdoc)
   * @see rex_i_package::getProperty()
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

  /* (non-PHPdoc)
   * @see rex_i_package::hasProperty()
   */
  public function hasProperty($key)
  {
    return is_string($key) && isset($this->properties[$key]);
  }

	/* (non-PHPdoc)
	 * @see rex_i_package::isAvailable()
	 */
	public function isAvailable()
  {
    return $this->isInstalled() && $this->isActivated();
  }

	/* (non-PHPdoc)
	 * @see rex_i_package::isInstalled()
	 */
	public function isInstalled()
  {
    return (boolean) $this->getProperty('install', false);
  }

	/* (non-PHPdoc)
	 * @see rex_i_package::isActivated()
	 */
	public function isActivated()
  {
    return (boolean) $this->getProperty('status', false);
  }

	/* (non-PHPdoc)
	 * @see rex_i_package::isSystemPackage()
	 */
	public function isSystemPackage()
  {
    global $REX;
    return in_array($this->getPackageId(), $REX['SYSTEM_PACKAGES']);
  }

  /* (non-PHPdoc)
   * @see rex_i_package::getAuthor()
   */
  public function getAuthor($default = null)
  {
    return $this->getProperty('author', $default);
  }

  /* (non-PHPdoc)
   * @see rex_i_package::getVersion()
   */
  public function getVersion($default = null)
  {
    return $this->getProperty('version', $default);
  }

  /* (non-PHPdoc)
   * @see rex_i_package::getSupportPage()
   */
  public function getSupportPage($default = null)
  {
    return $this->getProperty('supportpage', $default);
  }

  /* (non-PHPdoc)
   * @see rex_i_package::includeFile()
   */
  public function includeFile($file)
  {
    global $REX;
    include $this->getBasePath($file);
  }
}