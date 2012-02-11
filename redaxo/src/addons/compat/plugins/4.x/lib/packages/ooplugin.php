<?php

/**
 * @see rex_plugin
 *
 * @deprecated 5.0
 */
class OOPlugin
{
  /**
   * @see rex_package::isAvailable()
   *
   * @deprecated 5.0
   */
  static public function isAvailable($addon, $plugin)
  {
    return rex_plugin::get($addon, $plugin)->isAvailable();
  }

  /**
   * @see rex_package::isActivated()
   *
   * @deprecated 5.0
   */
  static public function isActivated($addon, $plugin)
  {
    return rex_plugin::get($addon, $plugin)->isActivated();
  }

  /**
   * @see rex_package::isInstalled()
   *
   * @deprecated 5.0
   */
  static public function isInstalled($addon, $plugin)
  {
    return rex_plugin::get($addon, $plugin)->isInstalled();
  }

  /**
   * @see rex_package::getVersion()
   *
   * @deprecated 5.0
   */
  static public function getVersion($addon, $plugin, $default = null)
  {
    return rex_plugin::get($addon, $plugin)->getVersion($default);
  }

  /**
   * @see rex_package::getAuthor()
   *
   * @deprecated 5.0
   */
  static public function getAuthor($addon, $plugin, $default = null)
  {
    return rex_plugin::get($addon, $plugin)->getAuthor($default);
  }

  /**
   * @see rex_package::getSupportPage()
   *
   * @deprecated 5.0
   */
  static public function getSupportPage($addon, $plugin, $default = null)
  {
    return rex_plugin::get($addon, $plugin)->getSupportPage($default);
  }

  /**
   * @see rex_package::setProperty()
   *
   * @deprecated 5.0
   */
  static public function setProperty($addon, $plugin, $property, $value)
  {
    rex_plugin::get($addon, $plugin)->setProperty($property, $value);
  }

  /**
   * @see rex_package::getProperty()
   *
   * @deprecated 5.0
   */
  static public function getProperty($addon, $plugin, $property, $default = null)
  {
    return rex_plugin::get($addon, $plugin)->getProperty($property, $default);
  }

  /**
   * @see rex_addon::getAvailablePlugins()
   *
   * @deprecated 5.0
   */
  static public function getAvailablePlugins($addon)
  {
    return rex_addon::get($addon)->getAvailablePlugins();
  }

  /**
   * @see rex_addon::getInstalledPlugins()
   *
   * @deprecated 5.0
   */
  static public function getInstalledPlugins($addon)
  {
    return rex_addon::get($addon)->getInstalledPlugins();
  }

  /**
   * @see rex_addon::getRegisteredPlugins()
   *
   * @deprecated 5.0
   */
  static public function getRegisteredPlugins($addon)
  {
    return rex_addon::get($addon)->getRegisteredPlugins();
  }
}
