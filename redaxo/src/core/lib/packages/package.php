<?php

/**
 * Abstract base class for packages
 *
 * @author gharlan
 */
abstract class rex_package implements rex_package_interface
{
  const
    FILE_PACKAGE       = 'package.yml',
    FILE_BOOT          = 'boot.php',
    FILE_INSTALL       = 'install.php',
    FILE_INSTALL_SQL   = 'install.sql',
    FILE_UNINSTALL     = 'uninstall.php',
    FILE_UNINSTALL_SQL = 'uninstall.sql',
    FILE_UPDATE        = 'update.php';

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
   * @throws rex_exception
   * @return rex_package
   */
  static public function get($packageId)
  {
    if (!is_string($packageId)) {
      throw new rex_exception('Expecting $packageId to be string, but ' . gettype($packageId) . ' given!');
    }
    $package = explode('/', $packageId);
    $addon = rex_addon::get($package[0]);
    if (isset($package[1])) {
      return $addon->getPlugin($package[1]);
    }
    return $addon;
  }

  /**
   * Returns if the package exists
   *
   * @param string $packageId Package ID
   * @return boolean
   */
  static public function exists($packageId)
  {
    $package = explode('/', $packageId);
    if (isset($package[1])) {
      return rex_plugin::exists($package[0], $package[1]);
    }
    return rex_addon::exists($package[0]);
  }

  /**
   * {@inheritDoc}
   */
  public function getName()
  {
    return $this->name;
  }

  /**
   * {@inheritDoc}
   */
  public function setConfig($key, $value = null)
  {
    return rex_config::set($this->getPackageId(), $key, $value);
  }

  /**
   * {@inheritDoc}
   */
  public function getConfig($key = null, $default = null)
  {
    return rex_config::get($this->getPackageId(), $key, $default);
  }

  /**
   * {@inheritDoc}
   */
  public function hasConfig($key = null)
  {
    return rex_config::has($this->getPackageId(), $key);
  }

  /**
   * {@inheritDoc}
   */
  public function removeConfig($key)
  {
    return rex_config::remove($this->getPackageId(), $key);
  }

  /**
   * {@inheritDoc}
   */
  public function setProperty($key, $value)
  {
    if (!is_string($key)) {
      throw new rex_exception('Expecting $key to be string, but ' . gettype($key) . ' given!');
    }
    $this->properties[$key] = $value;
  }

  /**
   * {@inheritDoc}
   */
  public function getProperty($key, $default = null)
  {
    if ($this->hasProperty($key)) {
      return $this->properties[$key];
    }
    return $default;
  }

  /**
   * {@inheritDoc}
   */
  public function hasProperty($key)
  {
    if (!is_string($key)) {
      throw new rex_exception('Expecting $key to be string, but ' . gettype($key) . ' given!');
    }
    if (!isset($this->properties[$key]) && !$this->propertiesLoaded) {
      $this->loadProperties();
    }
    return isset($this->properties[$key]);
  }

  /**
   * {@inheritDoc}
   */
  public function removeProperty($key)
  {
    if (!is_string($key)) {
      throw new rex_exception('Expecting $key to be string, but ' . gettype($key) . ' given!');
    }
    unset($this->properties[$key]);
  }

  /**
   * {@inheritDoc}
   */
  public function isAvailable()
  {
    return $this->isInstalled() && $this->isActivated();
  }

  /**
   * {@inheritDoc}
   */
  public function isInstalled()
  {
    return (boolean) $this->getProperty('install', false);
  }

  /**
   * {@inheritDoc}
   */
  public function isActivated()
  {
    return (boolean) $this->getProperty('status', false);
  }

  /**
   * {@inheritDoc}
   */
  public function getAuthor($default = null)
  {
    return $this->getProperty('author', $default);
  }

  /**
   * {@inheritDoc}
   */
  public function getVersion($default = null)
  {
    return $this->getProperty('version', $default);
  }

  /**
   * {@inheritDoc}
   */
  public function getSupportPage($default = null)
  {
    return $this->getProperty('supportpage', $default);
  }

  /**
   * {@inheritDoc}
   */
  public function includeFile($file)
  {
    include $this->getPath($file);
  }

  /**
   * Loads the properties of package.yml
   */
  private function loadProperties()
  {
    $properties = rex_file::getConfig($this->getPath(self::FILE_PACKAGE));
    foreach ($properties as $key => $value) {
      if (!isset($this->properties[$key]))
        $this->properties[$key] = rex_i18n::translateArray($value, false, array($this, 'i18n'));
    }
    $this->propertiesLoaded = true;
  }

  /**
   * Returns the registered packages
   *
   * @return rex_package[]
   */
  static public function getRegisteredPackages()
  {
    return self::getPackages('Registered');
  }

  /**
   * Returns the installed packages
   *
   * @return rex_package[]
   */
  static public function getInstalledPackages()
  {
    return self::getPackages('Installed');
  }

  /**
   * Returns the available packages
   *
   * @return rex_package[]
   */
  static public function getAvailablePackages()
  {
    return self::getPackages('Available');
  }

  /**
   * Returns the setup packages
   *
   * @return rex_package[]
   */
  static public function getSetupPackages()
  {
    return self::getPackages('Setup', 'System');
  }

  /**
   * Returns the system packages
   *
   * @return rex_package[]
   */
  static public function getSystemPackages()
  {
    return self::getPackages('System');
  }

  /**
   * Returns the packages by the given method
   *
   * @param string $method       Method
   * @param string $pluginMethod Optional other method for plugins
   * @return rex_package[]
   */
  static private function getPackages($method, $pluginMethod = null)
  {
    $packages = array();
    $addonMethod = 'get' . $method . 'Addons';
    $pluginMethod = 'get' . ($pluginMethod ?: $method) . 'Plugins';
    foreach (rex_addon::$addonMethod() as $addon) {
      $packages[$addon->getPackageId()] = $addon;
      foreach ($addon->$pluginMethod() as $plugin) {
        $packages[$plugin->getPackageId()] = $plugin;
      }
    }
    return $packages;
  }
}
