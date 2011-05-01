<?php

/**
 * Class for addons
 *
 * @author gharlan
 */
class rex_addon extends rex_package
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
   * @see rex_package::getAddon()
   */
  public function getAddon()
  {
    return $this;
  }

  /* (non-PHPdoc)
   * @see rex_package::getPackageRepresentation()
   */
  public function getPackageRepresentation()
  {
    return $this->getName();
  }

  /* (non-PHPdoc)
   * @see rex_package::getBasePath()
   */
  public function getBasePath($file = '')
  {
    return rex_path::addon($this->getName(), $file);
  }

  /* (non-PHPdoc)
   * @see rex_package::getAssetsPath()
   */
  public function getAssetsPath($file = '')
  {
    return rex_path::addonAssets($this->getName(), $file);
  }

  /* (non-PHPdoc)
   * @see rex_package::getDataPath()
   */
  public function getDataPath($file = '')
  {
    return rex_path::addonData($this->getName(), $file);
  }

  /* (non-PHPdoc)
   * @see rex_package::getConfigNamespace()
   */
  public function getConfigNamespace()
  {
    return $this->getName();
  }

  /**
   * Returns the child plugin by the given name
   *
   * @param string $plugin Name of the plugin
   *
   * @return rex_plugin
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

  /**
   * Returns if the plugin exists
   *
   * @param string $plugin Name of the plugin
   *
   * @return boolean
   */
  public function pluginExists($plugin)
  {
    return is_string($plugin) && isset($this->plugins[$plugins]);
  }

  /**
   * Returns the registered plugins
   *
   * @return array[rex_plugin]
   */
  public function getRegisteredPlugins()
  {
    return $this->plugins;
  }

  /**
   * Returns the installed plugins
   *
   * @return array[rex_plugin]
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

	/**
   * Returns the available plugins
   *
   * @return array[rex_plugin]
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
    foreach($config['install'] as $addonName => $install)
    {
      $addon = new rex_addon($addonName);
      $addon->setProperty('install', $install);
      $addon->setProperty('status', $config['status'][$addonName]);
      self::$addons[$addonName] = $addon;
      if(isset($config['plugins'][$addonName]['install']) && is_array($config['plugins'][$addonName]['install']))
      {
        foreach($config['plugins'][$addonName]['install'] as $pluginName => $pluginInstall)
        {
          $plugin = new rex_plugin($pluginName, $addon);
          $plugin->setProperty('install', $pluginInstall);
          $plugin->setProperty('status', $config['plugins'][$addonName]['status'][$pluginName]);
          $addon->plugins[$pluginName] = $plugin;
        }
      }
    }
  }
}


/**
 * Represents a dummy addon that doesn't exists in file system
 *
 * @author gharlan
 */
class rex_nullAddon extends rex_addon implements rex_nullPackage
{
  /**
   * Singleton instance
   *
   * @var rex_nullAddon;
   */
  static private $instance;

  /**
   * Constructor
   */
  public function __construct()
  {
    parent::__construct('nullAddon');
    $this->setConfig('install', false);
    $this->setConfig('status', false);
  }

  /* (non-PHPdoc)
   * @see rex_nullPackage::getInstance()
   */
  static public function getInstance()
  {
    if(!is_object(self::$instance))
    {
      self::$instance = new self;
    }
    return self::$instance;
  }
}