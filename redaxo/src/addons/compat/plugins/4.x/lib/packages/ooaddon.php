<?php

/**
 * @see rex_addon
 *
 * @deprecated 5.0
 */
class OOAddon
{
  /**
   * @see rex_package::isAvailable()
   *
   * @deprecated 5.0
   */
  static public function isAvailable($addon)
  {
    return rex_addon::get($addon)->isAvailable();
  }

  /**
   * @see rex_package::isActivated()
   *
   * @deprecated 5.0
   */
  static public function isActivated($addon)
  {
    return rex_addon::get($addon)->isActivated();
  }

  /**
   * @see rex_package::isInstalled()
   *
   * @deprecated 5.0
   */
  static public function isInstalled($addon)
  {
    return rex_addon::get($addon)->isInstalled();
  }

  /**
   * @see rex_package::getVersion()
   *
   * @deprecated 5.0
   */
  static public function getVersion($addon, $default = null)
  {
    return rex_addon::get($addon)->getVersion($default);
  }

  /**
   * @see rex_package::getAuthor()
   *
   * @deprecated 5.0
   */
  static public function getAuthor($addon, $default = null)
  {
    return rex_addon::get($addon)->getAuthor($default);
  }

  /**
   * @see rex_package::getSupportPage()
   *
   * @deprecated 5.0
   */
  static public function getSupportPage($addon, $default = null)
  {
    return rex_addon::get($addon)->getSupportPage($default);
  }

  /**
   * @see rex_package::setProperty()
   *
   * @deprecated 5.0
   */
  static public function setProperty($addon, $property, $value)
  {
    rex_addon::get($addon)->setProperty($property, $value);
  }

  /**
   * @see rex_package::getProperty()
   *
   * @deprecated 5.0
   */
  static public function getProperty($addon, $property, $default = null)
  {
    return rex_addon::get($addon)->getProperty($property, $default);
  }

  /**
   * @see rex_package::isSystemPackage()
   *
   * @deprecated 5.0
   */
  static public function isSystemAddon($addon)
  {
    return rex_addon::get($addon)->isSystemPackage();
  }

  /**
   * @see rex_addon::getAvailableAddons()
   *
   * @deprecated 5.0
   */
  static public function getAvailableAddons()
  {
    return rex_addon::getAvailableAddons();
  }

  /**
   * @see rex_addon::getRegisteredAddons()
   *
   * @deprecated 5.0
   */
  static public function getRegisteredAddons()
  {
    return rex_addon::getRegisteredAddons();
  }
}
