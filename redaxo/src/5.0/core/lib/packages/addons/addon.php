<?php

/**
 * Class for addons
 *
 * @author gharlan
 */
class rex_addon extends rex_package implements rex_i_addon
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
   * @see rex_i_package::getAddon()
   */
  public function getAddon()
  {
    return $this;
  }

  /* (non-PHPdoc)
   * @see rex_i_package::getPackageId()
   */
  public function getPackageId()
  {
    return $this->getName();
  }

  /* (non-PHPdoc)
   * @see rex_i_package::getBasePath()
   */
  public function getBasePath($file = '')
  {
    return rex_path::addon($this->getName(), $file);
  }

  /* (non-PHPdoc)
   * @see rex_i_package::getAssetsPath()
   */
  public function getAssetsPath($file = '')
  {
    return rex_path::addonAssets($this->getName(), $file);
  }

  /* (non-PHPdoc)
   * @see rex_i_package::getDataPath()
   */
  public function getDataPath($file = '')
  {
    return rex_path::addonData($this->getName(), $file);
  }

  /* (non-PHPdoc)
   * @see rex_i_addon::getPlugin()
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
   * @see rex_i_addon::pluginExists()
   */
  public function pluginExists($plugin)
  {
    return is_string($plugin) && isset($this->plugins[$plugin]);
  }

  /* (non-PHPdoc)
   * @see rex_i_addon::getRegisteredPlugins()
   */
  public function getRegisteredPlugins()
  {
    return $this->plugins;
  }

  /* (non-PHPdoc)
   * @see rex_i_addon::getInstalledPlugins()
   */
  public function getInstalledPlugins()
  {
    return array_filter($this->plugins,
      function(rex_plugin $plugin)
      {
        return $plugin->isInstalled();
      }
    );
  }

  /* (non-PHPdoc)
   * @see rex_i_addon::getAvailablePlugins()
   */
  public function getAvailablePlugins()
  {
    return array_filter($this->plugins,
      function(rex_plugin $plugin)
      {
        return $plugin->isAvailable();
      }
    );
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
    return array_filter(self::$addons,
      function(rex_addon $addon)
      {
        return $addon->isAvailable();
      }
    );
  }

  /**
   * Returns the available addons
   *
   * @return array[rex_addon]
   */
  static public function getAvailableAddons()
  {
    return array_filter(self::$addons,
      function(rex_addon $addon)
      {
        return $addon->isAvailable();
      }
    );
  }

  /**
   * Initializes all packages
   */
  static public function initialize()
  {
    self::$addons = array();
    $config = rex_core_config::get('package-config');
    foreach($config as $addonName => $addonConfig)
    {
      $addon = new rex_addon($addonName);
      $addon->setProperty('install', $addonConfig['install']);
      $addon->setProperty('status', $addonConfig['status']);
      self::$addons[$addonName] = $addon;
      if(isset($config[$addonName]['plugins']) && is_array($config[$addonName]['plugins']))
      {
        foreach($config[$addonName]['plugins'] as $pluginName => $pluginConfig)
        {
          $plugin = new rex_plugin($pluginName, $addon);
          $plugin->setProperty('install', $pluginConfig['install']);
          $plugin->setProperty('status', $pluginConfig['status']);
          $addon->plugins[$pluginName] = $plugin;
        }
      }
    }
  }
}