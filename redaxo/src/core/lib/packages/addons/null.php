<?php

/**
 * Represents a null addon
 *
 * @author gharlan
 */
class rex_null_addon extends rex_null_package implements rex_addon_interface
{
  /* (non-PHPdoc)
   * @see rex_package_interface::getType()
   */
  public function getType()
  {
    return 'addon';
  }

  /* (non-PHPdoc)
   * @see rex_addon_interface::getPlugin()
   */
  public function getPlugin($plugin)
  {
    return rex_null_plugin::getInstance();
  }

  /* (non-PHPdoc)
   * @see rex_addon_interface::pluginExists()
   */
  public function pluginExists($plugin)
  {
    return false;
  }

  /* (non-PHPdoc)
   * @see rex_addon_interface::getRegisteredPlugins()
   */
  public function getRegisteredPlugins()
  {
    return array();
  }

  /* (non-PHPdoc)
   * @see rex_addon_interface::getInstalledPlugins()
   */
  public function getInstalledPlugins()
  {
    return array();
  }

  /* (non-PHPdoc)
   * @see rex_addon_interface::getAvailablePlugins()
   */
  public function getAvailablePlugins()
  {
    return array();
  }

  /* (non-PHPdoc)
  * @see rex_addon_interface::getSystemPlugins()
  */
  public function getSystemPlugins()
  {
    return array();
  }
}
