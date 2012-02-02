<?php

/**
 * Class for plugins
 *
 * @author gharlan
 */
class rex_plugin extends rex_package implements rex_plugin_interface
{
  /**
   * Parent addon
   *
   * @var rex_addon
   */
  private $addon;

  /**
   * Constructor
   *
   * @param string $name Name
   * @param rex_addon $addon Parent addon
   */
  public function __construct($name, rex_addon $addon)
  {
    parent::__construct($name);
    $this->addon = $addon;
  }

  /**
   * Returns the plugin by the given name
   *
   * @param string $addon Name of the addon
   * @param string $plugin Name of the plugin
   *
   * @return rex_plugin
   */
  static public function get($addon, $plugin = null)
  {
    if($plugin === null)
    {
      throw new InvalidArgumentException('Missing Argument 2 for '. __CLASS__ .'::'. __METHOD__ .'()');
    }
    if(!is_string($addon))
    {
      throw new rex_exception('Expecting $addon to be string, but '. gettype($addon) .' given!');
    }
    if(!is_string($plugin))
    {
      throw new rex_exception('Expecting $plugin to be string, but '. gettype($plugin) .' given!');
    }
    return rex_addon::get($addon)->getPlugin($plugin);
  }

  /**
   * Returns if the plugin exists
   *
   * @param string $addon Name of the addon
   * @param string $plugin Name of the plugin
   *
   * @return boolean
   */
  static public function exists($addon, $plugin = null)
  {
    return rex_addon::exists($addon) && rex_addon::get($addon)->pluginExists($plugin);
  }

  /* (non-PHPdoc)
   * @see rex_package_interface::getAddon()
   */
  public function getAddon()
  {
    return $this->addon;
  }

  /* (non-PHPdoc)
   * @see rex_package_interface::getPackageId()
   */
  public function getPackageId()
  {
    return $this->getAddon()->getName() .'/'. $this->getName();
  }

  /* (non-PHPdoc)
   * @see rex_package_interface::getBasePath()
   */
  public function getBasePath($file = '')
  {
    return rex_path::plugin($this->getAddon()->getName(), $this->getName(), $file);
  }

  /* (non-PHPdoc)
   * @see rex_package_interface::getAssetsPath()
   */
  public function getAssetsPath($file = '', $pathType = rex_path::RELATIVE)
  {
    return rex_path::pluginAssets($this->getAddon()->getName(), $this->getName(), $file, $pathType);
  }

  /* (non-PHPdoc)
   * @see rex_package_interface::getDataPath()
   */
  public function getDataPath($file = '')
  {
    return rex_path::pluginData($this->getAddon()->getName(), $this->getName(), $file);
  }

  /* (non-PHPdoc)
   * @see rex_package_interface::getCachePath()
   */
  public function getCachePath($file = '')
  {
    return rex_path::pluginCache($this->getAddon()->getName(), $this->getName(), $file);
  }

  /* (non-PHPdoc)
   * @see rex_package_interface::isAvailable()
   */
  public function isAvailable()
  {
    return $this->getAddon()->isAvailable() && parent::isAvailable();
  }

  /**
   * Returns the registered plugins of the given addon
   *
   * @param string $addon Addon name
   *
   * @return array[rex_plugin]
   */
  static public function getRegisteredPlugins($addon)
  {
    return rex_addon::get($addon)->getRegisteredPlugins();
  }

  /**
   * Returns the installed plugins of the given addons
   *
   * @param string $addon Addon name
   *
   * @return array[rex_plugin]
   */
  static public function getInstalledPlugins($addon)
  {
    return rex_addon::get($addon)->getInstalledPlugins();
  }

  /**
   * Returns the available plugins of the given addons
   *
   * @param string $addon Addon name
   *
   * @return array[rex_plugin]
   */
  static public function getAvailablePlugins($addon)
  {
    return rex_addon::get($addon)->getAvailablePlugins();
  }
}