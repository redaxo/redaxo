<?php

/**
 * Represents a nullAddon
 *
 * @author gharlan
 */
class rex_nullAddon extends rex_nullPackage implements rex_i_addon
{
  static protected $instance;

  /* (non-PHPdoc)
   * @see rex_i_addon::getPlugin()
   */
  public function getPlugin($plugin)
  {
    return rex_nullPlugin::getInstance();
  }

  /* (non-PHPdoc)
   * @see rex_i_addon::pluginExists()
   */
  public function pluginExists($plugin)
  {
    return false;
  }

  /* (non-PHPdoc)
   * @see rex_i_addon::getRegisteredPlugins()
   */
  public function getRegisteredPlugins()
  {
    return array();
  }

  /* (non-PHPdoc)
   * @see rex_i_addon::getInstalledPlugins()
   */
  public function getInstalledPlugins()
  {
    return array();
  }

  /* (non-PHPdoc)
   * @see rex_i_addon::getAvailablePlugins()
   */
  public function getAvailablePlugins()
  {
    return array();
  }
}