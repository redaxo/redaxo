<?php

/**
 * Class for plugins
 *
 * @author gharlan
 */
class rex_plugin extends rex_package
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
      throw new rexException('Expecting $addon to be string, but '. gettype($addon) .' given!');
    }
    if(!is_string($plugin))
    {
      throw new rexException('Expecting $plugin to be string, but '. gettype($plugin) .' given!');
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
   * @see rex_package::getAddon()
   */
  public function getAddon()
  {
    return $this->addon;
  }

  /* (non-PHPdoc)
   * @see rex_package::getPackageRepresentation()
   */
  public function getPackageRepresentation()
  {
    return array($this->getAddon()->getName(), $this->getName());
  }

  /* (non-PHPdoc)
   * @see rex_package::getBasePath()
   */
  public function getBasePath($file = '')
  {
    return rex_path::plugin($this->getAddon()->getName(), $this->getName(), $file);
  }

  /* (non-PHPdoc)
   * @see rex_package::getAssetsPath()
   */
  public function getAssetsPath($file = '')
  {
    return rex_path::pluginAssets($this->getAddon()->getName(), $this->getName(), $file);
  }

  /* (non-PHPdoc)
   * @see rex_package::getDataPath()
   */
  public function getDataPath($file = '')
  {
    return rex_path::pluginData($this->getAddon()->getName(), $this->getName(), $file);
  }

  /* (non-PHPdoc)
   * @see rex_package::getConfigNamespace()
   */
  public function getConfigNamespace()
  {
    return $this->getAddon()->getName() .'/'. $this->getName();
  }

  /* (non-PHPdoc)
   * @see rex_package::isAvailable()
   */
  public function isAvailable()
  {
    return $this->getAddon()->isAvailable() && parent::isAvailable();
  }
}


/**
 * Represents a dummy plugin that doesn't exists in file system
 *
 * @author gharlan
 */
class rex_nullPlugin extends rex_plugin implements rex_nullPackage
{
  /**
   * Constructor
   *
   * @param string $name Name
   * @param rex_addon $addon Parent addon
   */
  public function __construct($name, rex_addon $addon)
  {
    parent::__construct($name, $addon);
    $this->setConfig('install', false);
    $this->setConfig('status', false);
  }
}