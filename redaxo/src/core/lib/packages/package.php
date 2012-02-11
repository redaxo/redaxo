<?php

/**
 * Abstract base class for packages
 *
 * @author gharlan
 */
abstract class rex_package implements rex_package_interface
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
   * Flag whether the properties of package.yml are loaded
   *
   * @var boolean
   */
  private $propertiesLoaded = false;

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
      throw new rex_exception('Expecting $packageId to be string, but '. gettype($packageId) .' given!');
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
   * @see rex_package_interface::getName()
   */
  public function getName()
  {
    return $this->name;
  }

  /* (non-PHPdoc)
   * @see rex_package_interface::setConfig()
   */
  public function setConfig($key, $value)
  {
    return rex_config::set($this->getPackageId(), $key, $value);
  }

  /* (non-PHPdoc)
   * @see rex_package_interface::getConfig()
   */
  public function getConfig($key, $default = null)
  {
    return rex_config::get($this->getPackageId(), $key, $default);
  }

  /* (non-PHPdoc)
   * @see rex_package_interface::hasConfig()
   */
  public function hasConfig($key = null)
  {
    return rex_config::has($this->getPackageId(), $key);
  }

  /* (non-PHPdoc)
   * @see rex_package_interface::removeConfig()
   */
  public function removeConfig($key)
  {
    return rex_config::remove($this->getPackageId(), $key);
  }

  /* (non-PHPdoc)
   * @see rex_package_interface::setProperty()
   */
  public function setProperty($key, $value)
  {
    if(!is_string($key))
    {
      throw new rex_exception('Expecting $key to be string, but '. gettype($key) .' given!');
    }
    $this->properties[$key] = $value;
  }

  /* (non-PHPdoc)
   * @see rex_package_interface::getProperty()
   */
  public function getProperty($key, $default = null)
  {
    if($this->hasProperty($key))
    {
      return $this->properties[$key];
    }
    return $default;
  }

  /* (non-PHPdoc)
   * @see rex_package_interface::hasProperty()
   */
  public function hasProperty($key)
  {
    if(!is_string($key))
    {
      throw new rex_exception('Expecting $key to be string, but '. gettype($key) .' given!');
    }
    if(!isset($this->properties[$key]) && !$this->propertiesLoaded)
    {
      $this->loadProperties();
    }
    return isset($this->properties[$key]);
  }

  /* (non-PHPdoc)
   * @see rex_package_interface::removeProperty()
   */
  public function removeProperty($key)
  {
    if(!is_string($key))
    {
      throw new rex_exception('Expecting $key to be string, but '. gettype($key) .' given!');
    }
    unset($this->properties[$key]);
  }

  /* (non-PHPdoc)
   * @see rex_package_interface::isAvailable()
   */
  public function isAvailable()
  {
    return $this->isInstalled() && $this->isActivated();
  }

  /* (non-PHPdoc)
   * @see rex_package_interface::isInstalled()
   */
  public function isInstalled()
  {
    return (boolean) $this->getProperty('install', false);
  }

  /* (non-PHPdoc)
   * @see rex_package_interface::isActivated()
   */
  public function isActivated()
  {
    return (boolean) $this->getProperty('status', false);
  }

  /* (non-PHPdoc)
   * @see rex_package_interface::isSystemPackage()
   */
  public function isSystemPackage()
  {
    return in_array($this->getPackageId(), rex::getProperty('system_packages'));
  }

  /* (non-PHPdoc)
   * @see rex_package_interface::getAuthor()
   */
  public function getAuthor($default = null)
  {
    return $this->getProperty('author', $default);
  }

  /* (non-PHPdoc)
   * @see rex_package_interface::getVersion()
   */
  public function getVersion($default = null)
  {
    return $this->getProperty('version', $default);
  }

  /* (non-PHPdoc)
   * @see rex_package_interface::getSupportPage()
   */
  public function getSupportPage($default = null)
  {
    return $this->getProperty('supportpage', $default);
  }

  /* (non-PHPdoc)
   * @see rex_package_interface::includeFile()
   */
  public function includeFile($file)
  {
    include $this->getBasePath($file);
  }

  /**
   * Loads the properties of package.yml
   */
  private function loadProperties()
  {
    $properties = rex_file::getConfig($this->getBasePath('package.yml'));
    foreach($properties as $key => $value)
    {
      if(!isset($this->properties[$key]))
        $this->properties[$key] = rex_i18n::translateArray($value, true, array($this, 'i18n'));
    }
    $this->propertiesLoaded = true;
  }
}
