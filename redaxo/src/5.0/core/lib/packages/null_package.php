<?php

/**
 * Represents a nullPackage
 *
 * @author gharlan
 */
abstract class rex_nullPackage implements rex_packageInterface
{
  /**
   * Singleton instance
   *
   * @var array[rex_nullPackage];
   */
  static private $instances = array();

  /**
   * Returns the singleton instance
   *
   * @return rex_nullPackage
   */
  static public function getInstance()
  {
    $class = get_called_class();
    if(!isset(self::$instances[$class]))
    {
      self::$instances[$class] = new static;
    }
    return self::$instances[$class];
  }

  /* (non-PHPdoc)
   * @see rex_packageInterface::getName()
   */
  public function getName()
  {
    return getClass($this);
  }

  /* (non-PHPdoc)
   * @see rex_packageInterface::getAddon()
   */
  public function getAddon()
  {
    return rex_nullAddon::getInstance();
  }

  /* (non-PHPdoc)
   * @see rex_packageInterface::getPackageId()
   */
  public function getPackageId()
  {
    return null;
  }

  /* (non-PHPdoc)
   * @see rex_packageInterface::getBasePath()
   */
  public function getBasePath($file = '')
  {
    return null;
  }

  /* (non-PHPdoc)
   * @see rex_packageInterface::getAssetsPath()
   */
  public function getAssetsPath($file = '')
  {
    return null;
  }

  /* (non-PHPdoc)
   * @see rex_packageInterface::getDataPath()
   */
  public function getDataPath($file = '')
  {
    return null;
  }

  /* (non-PHPdoc)
   * @see rex_packageInterface::setConfig()
   */
  public function setConfig($key, $value)
  {
  }

  /* (non-PHPdoc)
   * @see rex_packageInterface::getConfig()
   */
  public function getConfig($key, $default = null)
  {
    return $default;
  }

  /* (non-PHPdoc)
   * @see rex_packageInterface::hasConfig()
   */
  public function hasConfig($key)
  {
    return false;
  }

  /* (non-PHPdoc)
   * @see rex_packageInterface::setProperty()
   */
  public function setProperty($key, $value)
  {
  }

  /* (non-PHPdoc)
   * @see rex_packageInterface::getProperty()
   */
  public function getProperty($key, $default = null)
  {
    return $default;
  }

  /* (non-PHPdoc)
   * @see rex_packageInterface::hasProperty()
   */
  public function hasProperty($key)
  {
    return false;
  }

  /* (non-PHPdoc)
   * @see rex_packageInterface::isAvailable()
   */
  public function isAvailable()
  {
    return false;
  }

  /* (non-PHPdoc)
   * @see rex_packageInterface::isInstalled()
   */
  public function isInstalled()
  {
    return false;
  }

  /* (non-PHPdoc)
   * @see rex_packageInterface::isActivated()
   */
  public function isActivated()
  {
    return false;
  }

  /* (non-PHPdoc)
   * @see rex_packageInterface::isSystemPackage()
   */
  public function isSystemPackage()
  {
    return false;
  }

  /* (non-PHPdoc)
   * @see rex_packageInterface::getAuthor()
   */
  public function getAuthor($default = null)
  {
    return $default;
  }

  /* (non-PHPdoc)
   * @see rex_packageInterface::getVersion()
   */
  public function getVersion($default = null)
  {
    return $default;
  }

  /* (non-PHPdoc)
   * @see rex_packageInterface::getSupportPage()
   */
  public function getSupportPage($default = null)
  {
    return $default;
  }

  /* (non-PHPdoc)
   * @see rex_packageInterface::includeFile()
   */
  public function includeFile($file, array $globals = array())
  {
  }
}