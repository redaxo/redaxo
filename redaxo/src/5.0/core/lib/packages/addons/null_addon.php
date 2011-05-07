<?php

/**
 * Represents a nullAddon
 *
 * @author gharlan
 */
class rex_nullAddon extends rex_nullPackage implements rex_addonInterface
{
  /* (non-PHPdoc)
   * @see rex_addonInterface::getPlugin()
   */
  public function getPlugin($plugin)
  {
    return rex_nullPlugin::getInstance();
  }

  /* (non-PHPdoc)
   * @see rex_addonInterface::pluginExists()
   */
  public function pluginExists($plugin)
  {
    return false;
  }

  /* (non-PHPdoc)
   * @see rex_addonInterface::getRegisteredPlugins()
   */
  public function getRegisteredPlugins()
  {
    return array();
  }

  /* (non-PHPdoc)
   * @see rex_addonInterface::getInstalledPlugins()
   */
  public function getInstalledPlugins()
  {
    return array();
  }

  /* (non-PHPdoc)
   * @see rex_addonInterface::getAvailablePlugins()
   */
  public function getAvailablePlugins()
  {
    return array();
  }
}