<?php

/**
 * Represents a null package
 *
 * @author gharlan
 */
abstract class rex_null_package extends rex_singleton implements rex_package_interface
{
  /* (non-PHPdoc)
   * @see rex_package_interface::getName()
   */
  public function getName()
  {
    return get_class($this);
  }

  /* (non-PHPdoc)
   * @see rex_package_interface::getAddon()
   */
  public function getAddon()
  {
    return rex_null_addon::getInstance();
  }

  /* (non-PHPdoc)
   * @see rex_package_interface::getPackageId()
   */
  public function getPackageId()
  {
    return null;
  }

  /* (non-PHPdoc)
   * @see rex_package_interface::getBasePath()
   */
  public function getBasePath($file = '')
  {
    return null;
  }

  /* (non-PHPdoc)
   * @see rex_package_interface::getAssetsPath()
   */
  public function getAssetsPath($file = '', $pathType = rex_path::RELATIVE)
  {
    return null;
  }

  /* (non-PHPdoc)
   * @see rex_package_interface::getDataPath()
   */
  public function getDataPath($file = '')
  {
    return null;
  }

  /* (non-PHPdoc)
   * @see rex_package_interface::setConfig()
   */
  public function setConfig($key, $value)
  {
    return false;
  }

  /* (non-PHPdoc)
   * @see rex_package_interface::getConfig()
   */
  public function getConfig($key, $default = null)
  {
    return $default;
  }

  /* (non-PHPdoc)
   * @see rex_package_interface::hasConfig()
   */
  public function hasConfig($key)
  {
    return false;
  }

  /* (non-PHPdoc)
   * @see rex_package_interface::removeConfig()
   */
  public function removeConfig($key)
  {
    return false;
  }

  /* (non-PHPdoc)
   * @see rex_package_interface::setProperty()
   */
  public function setProperty($key, $value)
  {
  }

  /* (non-PHPdoc)
   * @see rex_package_interface::getProperty()
   */
  public function getProperty($key, $default = null)
  {
    return $default;
  }

  /* (non-PHPdoc)
   * @see rex_package_interface::hasProperty()
   */
  public function hasProperty($key)
  {
    return false;
  }

  /* (non-PHPdoc)
   * @see rex_package_interface::removeProperty()
   */
  public function removeProperty($key)
  {
  }

  /* (non-PHPdoc)
   * @see rex_package_interface::isAvailable()
   */
  public function isAvailable()
  {
    return false;
  }

  /* (non-PHPdoc)
   * @see rex_package_interface::isInstalled()
   */
  public function isInstalled()
  {
    return false;
  }

  /* (non-PHPdoc)
   * @see rex_package_interface::isActivated()
   */
  public function isActivated()
  {
    return false;
  }

  /* (non-PHPdoc)
   * @see rex_package_interface::isSystemPackage()
   */
  public function isSystemPackage()
  {
    return false;
  }

  /* (non-PHPdoc)
   * @see rex_package_interface::getAuthor()
   */
  public function getAuthor($default = null)
  {
    return $default;
  }

  /* (non-PHPdoc)
   * @see rex_package_interface::getVersion()
   */
  public function getVersion($default = null)
  {
    return $default;
  }

  /* (non-PHPdoc)
   * @see rex_package_interface::getSupportPage()
   */
  public function getSupportPage($default = null)
  {
    return $default;
  }

  /* (non-PHPdoc)
   * @see rex_package_interface::includeFile()
   */
  public function includeFile($file)
  {
  }

  /* (non-PHPdoc)
  * @see rex_package_interface::i18n()
  */
  public function i18n($key)
  {
    $args = func_get_args();
    return call_user_func_array('rex_i18n::msg', $args);
  }
}