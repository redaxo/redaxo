<?php

/**
 * Class for addons
 *
 * @author gharlan
 */
class rex_addon extends rex_package implements rex_addonInterface
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
    if(!is_string($addon))
    {
      throw new rexException('Expecting $addon to be string, but '. gettype($addon) .' given!');
    }
    if(!isset(self::$addons[$addon]))
    {
      return rex_nullAddon::getInstance();
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
   * @see rex_packageInterface::getAddon()
   */
  public function getAddon()
  {
    return $this;
  }

  /* (non-PHPdoc)
   * @see rex_packageInterface::getPackageId()
   */
  public function getPackageId()
  {
    return $this->getName();
  }

  /* (non-PHPdoc)
   * @see rex_packageInterface::getBasePath()
   */
  public function getBasePath($file = '')
  {
    return rex_path::addon($this->getName(), $file);
  }

  /* (non-PHPdoc)
   * @see rex_packageInterface::getAssetsPath()
   */
  public function getAssetsPath($file = '')
  {
    return rex_path::addonAssets($this->getName(), $file);
  }

  /* (non-PHPdoc)
   * @see rex_packageInterface::getDataPath()
   */
  public function getDataPath($file = '')
  {
    return rex_path::addonData($this->getName(), $file);
  }

  /* (non-PHPdoc)
   * @see rex_addonInterface::getPlugin()
   */
  public function getPlugin($plugin)
  {
  	if(!is_string($plugin))
    {
      throw new rexException('Expecting $plugin to be string, but '. gettype($plugin) .' given!');
    }
    if(!isset($this->plugins[$plugin]))
    {
      return rex_nullPlugin::getInstance();
    }
    return $this->plugins[$plugin];
  }

  /* (non-PHPdoc)
   * @see rex_addonInterface::pluginExists()
   */
  public function pluginExists($plugin)
  {
    return is_string($plugin) && isset($this->plugins[$plugin]);
  }

  /* (non-PHPdoc)
   * @see rex_addonInterface::getRegisteredPlugins()
   */
  public function getRegisteredPlugins()
  {
    return $this->plugins;
  }

  /* (non-PHPdoc)
   * @see rex_addonInterface::getInstalledPlugins()
   */
  public function getInstalledPlugins()
  {
    return self::filterPackages($this->plugins, 'isInstalled');
  }

  /* (non-PHPdoc)
   * @see rex_addonInterface::getAvailablePlugins()
   */
  public function getAvailablePlugins()
  {
    return self::filterPackages($this->plugins, 'isAvailable');
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
   * Initializes all packages
   */
  static public function initialize()
  {
    $config = rex_core_config::get('package-config', array());
    foreach($config as $addonName => $addonConfig)
    {
      if(!isset(self::$addons[$addonName]))
      {
        self::$addons[$addonName] = new rex_addon($addonName);
      }
      $addon = self::$addons[$addonName];
      $addon->setProperty('install', $addonConfig['install']);
      $addon->setProperty('status', $addonConfig['status']);
      if(isset($config[$addonName]['plugins']) && is_array($config[$addonName]['plugins']))
      {
        foreach($config[$addonName]['plugins'] as $pluginName => $pluginConfig)
        {
          if(!isset($addon->plugins[$pluginName]))
          {
            $addon->plugins[$pluginName] = new rex_plugin($pluginName, $addon);
          }
          $plugin = $addon->plugins[$pluginName];
          $plugin->setProperty('install', $pluginConfig['install']);
          $plugin->setProperty('status', $pluginConfig['status']);
        }
      }
    }
  }

  /**
   * Filters packages by the given method
   *
   * @param array $packages Array of packages
   * @param string $method A rex_package method
   *
   * @return array[rex_package]
   */
  static private function filterPackages(array $packages, $method)
  {
    return array_filter($packages,
      function(rex_package $package) use ($method)
      {
        return $package->$method();
      }
    );
  }
}