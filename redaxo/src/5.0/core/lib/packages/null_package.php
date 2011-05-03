<?php

/**
 * Represents a nullPackage
 *
 * @author gharlan
 */
abstract class rex_nullPackage implements rex_i_package
{
  /**
   * Singleton instance
   *
   * @var rex_nullPackage;
   */
  static protected $instance;

  /**
   * Returns the singleton instance
   *
   * @return rex_nullPackage
   */
  static public function getInstance()
  {
    return static::$instance ?: static::$instance = new static;
  }

  /* (non-PHPdoc)
   * @see rex_i_package::getName()
   */
  public function getName()
  {
    return getClass($this);
  }

  /* (non-PHPdoc)
   * @see rex_i_package::getAddon()
   */
  public function getAddon()
  {
    return rex_nullAddon::getInstance();
  }

  /* (non-PHPdoc)
   * @see rex_i_package::getPackageId()
   */
  public function getPackageId()
  {
    return null;
  }

  /* (non-PHPdoc)
   * @see rex_i_package::getBasePath()
   */
  public function getBasePath($file = '')
  {
    return null;
  }

  /* (non-PHPdoc)
   * @see rex_i_package::getAssetsPath()
   */
  public function getAssetsPath($file = '')
  {
    return null;
  }

  /* (non-PHPdoc)
   * @see rex_i_package::getDataPath()
   */
  public function getDataPath($file = '')
  {
    return null;
  }

  /* (non-PHPdoc)
   * @see rex_i_package::setConfig()
   */
  public function setConfig($key, $value)
  {
  }

  /* (non-PHPdoc)
   * @see rex_i_package::getConfig()
   */
  public function getConfig($key, $default = null)
  {
    return $default;
  }

  /* (non-PHPdoc)
   * @see rex_i_package::hasConfig()
   */
  public function hasConfig($key)
  {
    return false;
  }

  /* (non-PHPdoc)
   * @see rex_i_package::setProperty()
   */
  public function setProperty($key, $value)
  {
  }

  /* (non-PHPdoc)
   * @see rex_i_package::getProperty()
   */
  public function getProperty($key, $default = null)
  {
    return $default;
  }

  /* (non-PHPdoc)
   * @see rex_i_package::hasProperty()
   */
  public function hasProperty($key)
  {
    return false;
  }

  /* (non-PHPdoc)
   * @see rex_i_package::isAvailable()
   */
  public function isAvailable()
  {
    return false;
  }

  /* (non-PHPdoc)
   * @see rex_i_package::isInstalled()
   */
  public function isInstalled()
  {
    return false;
  }

  /* (non-PHPdoc)
   * @see rex_i_package::isActivated()
   */
  public function isActivated()
  {
    return false;
  }

  /* (non-PHPdoc)
   * @see rex_i_package::isSystemPackage()
   */
  public function isSystemPackage()
  {
    return false;
  }

  /* (non-PHPdoc)
   * @see rex_i_package::getAuthor()
   */
  public function getAuthor($default = null)
  {
    return $default;
  }

  /* (non-PHPdoc)
   * @see rex_i_package::getVersion()
   */
  public function getVersion($default = null)
  {
    return $default;
  }

  /* (non-PHPdoc)
   * @see rex_i_package::getSupportPage()
   */
  public function getSupportPage($default = null)
  {
    return $default;
  }

  /* (non-PHPdoc)
   * @see rex_i_package::includeFile()
   */
  public function includeFile($file, array $globals = array())
  {
  }
}