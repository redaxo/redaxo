<?php

/**
 * Class for addons
 *
 * @author gharlan
 */
class rex_addon extends rex_package implements rex_addon_interface
{
  /**
   * Array of all addons
   *
   * @var array[rex_addon]
   */
  static private $addons = array();

  /**
   * Array of all child plugins
   *
   * @var array[rex_plugin]
   */
  private $plugins = array();

  /**
   * Returns the addon by the given name
   *
   * @param string $addon Name of the addon
   *
   * @return rex_addon
   */
  static public function get($addon)
  {
    if (!is_string($addon)) {
      throw new rex_exception('Expecting $addon to be string, but ' . gettype($addon) . ' given!');
    }
    if (!isset(self::$addons[$addon])) {
      return rex_null_addon::getInstance();
    }
    return self::$addons[$addon];
  }

  /**
   * Returns if the addon exists
   *
   * @param string $addon Name of the addon
   *
   * @return boolean
   */
  static public function exists($addon)
  {
    return is_string($addon) && isset(self::$addons[$addon]);
  }

  /* (non-PHPdoc)
   * @see rex_package_interface::getAddon()
   */
  public function getAddon()
  {
    return $this;
  }

  /* (non-PHPdoc)
   * @see rex_package_interface::getPackageId()
   */
  public function getPackageId()
  {
    return $this->getName();
  }

  /* (non-PHPdoc)
   * @see rex_package_interface::getType()
   */
  public function getType()
  {
    return 'addon';
  }

  /* (non-PHPdoc)
   * @see rex_package_interface::getBasePath()
   */
  public function getBasePath($file = '')
  {
    return rex_path::addon($this->getName(), $file);
  }

  /* (non-PHPdoc)
   * @see rex_package_interface::getAssetsPath()
   */
  public function getAssetsPath($file = '', $pathType = rex_path::RELATIVE)
  {
    return rex_path::addonAssets($this->getName(), $file, $pathType);
  }

  /* (non-PHPdoc)
   * @see rex_package_interface::getDataPath()
   */
  public function getDataPath($file = '')
  {
    return rex_path::addonData($this->getName(), $file);
  }

  /* (non-PHPdoc)
   * @see rex_package_interface::getCachePath()
   */
  public function getCachePath($file = '')
  {
    return rex_path::addonCache($this->getName(), $file);
  }

  /* (non-PHPdoc)
   * @see rex_package_interface::isSystemPackage()
   */
  public function isSystemPackage()
  {
    return in_array($this->getPackageId(), rex::getProperty('system_addons'));
  }

  /* (non-PHPdoc)
   * @see rex_package_interface::i18n()
   */
  public function i18n($key)
  {
    $args = func_get_args();
    $key = $this->getName() . '_' . $key;
    if (rex_i18n::hasMsg($key)) {
      $args[0] = $key;
    }
    return call_user_func_array('rex_i18n::msg', $args);
  }

  /* (non-PHPdoc)
   * @see rex_addon_interface::getPlugin()
   */
  public function getPlugin($plugin)
  {
    if (!is_string($plugin)) {
      throw new rex_exception('Expecting $plugin to be string, but ' . gettype($plugin) . ' given!');
    }
    if (!isset($this->plugins[$plugin])) {
      return rex_null_plugin::getInstance();
    }
    return $this->plugins[$plugin];
  }

  /* (non-PHPdoc)
   * @see rex_addon_interface::pluginExists()
   */
  public function pluginExists($plugin)
  {
    return is_string($plugin) && isset($this->plugins[$plugin]);
  }

  /* (non-PHPdoc)
   * @see rex_addon_interface::getRegisteredPlugins()
   */
  public function getRegisteredPlugins()
  {
    return $this->plugins;
  }

  /* (non-PHPdoc)
   * @see rex_addon_interface::getInstalledPlugins()
   */
  public function getInstalledPlugins()
  {
    return self::filterPackages($this->plugins, 'isInstalled');
  }

  /* (non-PHPdoc)
   * @see rex_addon_interface::getAvailablePlugins()
   */
  public function getAvailablePlugins()
  {
    return self::filterPackages($this->plugins, 'isAvailable');
  }

  /* (non-PHPdoc)
   * @see rex_addon_interface::getSystemPlugins()
   */
  public function getSystemPlugins()
  {
    if (rex::isSetup() || rex::isSafeMode()) {
      // in setup and safemode this method is called before the package .lang files are added to rex_i18n
      // so don't use getProperty(), to avoid loading all properties without translations
      $properties = rex_file::getConfig($this->getBasePath('package.yml'));
      $systemPlugins = isset($properties['system_plugins']) ? (array) $properties['system_plugins'] : array();
    } else {
      $systemPlugins = (array) $this->getProperty('system_plugins', array());
    }
    $plugins = array();
    foreach ($systemPlugins as $plugin) {
      if ($this->pluginExists($plugin)) {
        $plugins[$plugin] = $this->getPlugin($plugin);
      }
    }
    return $plugins;
  }

  /**
   * Returns the registered addons
   *
   * @return array[rex_addon]
   */
  static public function getRegisteredAddons()
  {
    return self::$addons;
  }

  /**
   * Returns the installed addons
   *
   * @return array[rex_addon]
   */
  static public function getInstalledAddons()
  {
    return self::filterPackages(self::$addons, 'isInstalled');
  }

  /**
   * Returns the available addons
   *
   * @return array[rex_addon]
   */
  static public function getAvailableAddons()
  {
    return self::filterPackages(self::$addons, 'isAvailable');
  }

  /**
   * Returns the setup addons
   *
   * @return array[rex_addon]
   */
  static public function getSetupAddons()
  {
    $addons = array();
    foreach ((array) rex::getProperty('setup_addons', array()) as $addon) {
      if (self::exists($addon)) {
        $addons[$addon] = self::get($addon);
      }
    }
    return $addons;
  }

  /**
   * Returns the system addons
   *
   * @return array[rex_addon]
   */
  static public function getSystemAddons()
  {
    $addons = array();
    foreach ((array) rex::getProperty('system_addons', array()) as $addon) {
      if (self::exists($addon)) {
        $addons[$addon] = self::get($addon);
      }
    }
    return $addons;
  }

  /**
   * Initializes all packages
   */
  static public function initialize($dbExists = true)
  {
    if ($dbExists) {
      $config = rex::getConfig('package-config', array());
    } else {
      $config = array();
      foreach (rex::getProperty('setup_addons') as $addon) {
        $config[$addon]['install'] = false;
      }
    }
    $addons = self::$addons;
    self::$addons = array();
    foreach ($config as $addonName => $addonConfig) {
      $addon = isset($addons[$addonName]) ? $addons[$addonName] : new self($addonName);
      $addon->setProperty('install', isset($addonConfig['install']) ? $addonConfig['install'] : false);
      $addon->setProperty('status', isset($addonConfig['status']) ? $addonConfig['status'] : false);
      self::$addons[$addonName] = $addon;
      if (!$dbExists && is_array($plugins = $addon->getProperty('system_plugins'))) {
        foreach ($plugins as $plugin) {
          $config[$addonName]['plugins'][$plugin]['install'] = false;
        }
      }
      if (isset($config[$addonName]['plugins']) && is_array($config[$addonName]['plugins'])) {
        $plugins = $addon->plugins;
        $addon->plugins = array();
        foreach ($config[$addonName]['plugins'] as $pluginName => $pluginConfig) {
          $plugin = isset($plugins[$pluginName]) ? $plugins[$pluginName] : new rex_plugin($pluginName, $addon);
          $plugin->setProperty('install', isset($pluginConfig['install']) ? $pluginConfig['install'] : false);
          $plugin->setProperty('status', isset($pluginConfig['status']) ? $pluginConfig['status'] : false);
          $addon->plugins[$pluginName] = $plugin;
        }
      }
    }
  }

  /**
   * Filters packages by the given method
   *
   * @param array  $packages Array of packages
   * @param string $method   A rex_package method
   *
   * @return array[rex_package]
   */
  static private function filterPackages(array $packages, $method)
  {
    return array_filter($packages,
      function (rex_package $package) use ($method) {
        return $package->$method();
      }
    );
  }
}
