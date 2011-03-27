<?php

/**
 * Basisklasse für Addons/Plugins
 *
 * @package redaxo5
 * @version svn:$Id$
 */
class rex_package
{
  private
    $name,
    $data;

  /**
   * rex_package Konstruktor.
   *
   * @param string|array $package Namensraum des Packages
   */
  protected function __construct($package)
  {
    global $REX;

    // plugin?
    if(is_array($package))
    {
      if(!isset($package[0]) || !isset($package[1]) ||
         !is_string($package[0]) || !is_string($package[1]))
      {
        throw new rexException('Unexpected package format!');
      }

      $addon = $package[0];
      $plugin = $package[1];
      $this->data =& $REX['ADDON']['plugins'][$addon];
      $this->name = $plugin;
    }
    // addon?
    else
    {
      $this->data =& $REX['ADDON'];
      $this->name = $package;
    }
  }

  /**
   * Erstellt ein Packages aus dem Namespace $namespace.
   *
   * @param string|array $package Namensraum des Packages
   *
   * @return rex_package Zum namespace erstellte Package instanz
   */
  static protected function create($package)
  {
    if($package == null)
    {
      throw new InvalidArgumentException('Namespace must not be null!');
    }

    static $addons = array();

    $nsString = $package;
    if(is_array($package))
    {
      $nsString = implode('/', $package);
    }

    if(!isset($addons[$nsString]))
    {
      $addons[$nsString] = new rex_package($package);
    }

    return $addons[$nsString];
  }

  /**
   * Prüft ob das Package verfügbar ist, also installiert und aktiviert.
   *
   * @param string|array $package Name des Packages
   *
   * @return boolean TRUE, wenn das Package verfügbar ist, sonst FALSE
   */
  static public function isAvailable($package)
  {
    return self::isInstalled($package) && self::isActivated($package);
  }

  /**
   * Prüft ob das Package aktiviert ist.
   *
   * @param string|array $package Name des Packages
   *
   * @return boolean TRUE, wenn das Package aktiviert ist, sonst FALSE
   */
  static public function isActivated($package)
  {
    return self::getProperty($package, 'status', false) == true;
  }

  /**
   * Prüft ob das Package installiert ist.
   *
   * @param string|array $package Name des Packages
   *
   * @return boolean TRUE, wenn das Package installiert ist, sonst FALSE
   */
  static public function isInstalled($package)
  {
    return self::getProperty($package, 'install', false) == true;
  }

  /**
   * Gibt die Version des Packages zurück.
   *
   * @param string|array $package Name des Packages
   * @param mixed $default Rückgabewert, falls keine Version gefunden wurde
   *
   * @return string Versionsnummer des Packages
   */
  static public function getVersion($package, $default = null)
  {
    return self::getProperty($package, 'version', $default);
  }

  /**
   * Gibt den Autor des Packages zurück.
   *
   * @param string|array $package Name des Packages
   * @param mixed $default Rückgabewert, falls kein Autor gefunden wurde
   *
   * @return string Autor des Packages
   */
  static public function getAuthor($package, $default = null)
  {
    return self::getProperty($package, 'author', $default);
  }

  /**
   * Gibt die Support-Adresse des Packages zurück.
   *
   * @param string|array $package Name des Packages
   * @param mixed $default Rückgabewert, falls keine Support-Adresse gefunden wurde
   *
   * @return string Versionsnummer des Packages
   */
  static public function getSupportPage($package, $default = null)
  {
    return self::getProperty($package, 'supportpage', $default);
  }

  /**
   * Setzt eine Eigenschaft des Packages.
   *
   * @param string|array $package Name des Packages
   * @param string $property Name der Eigenschaft
   * @param mixed $property Wert der Eigenschaft
   *
   * @return string Versionsnummer des Packages
   */
  static public function setProperty($package, $property, $value)
  {
    $rexPackage = self::create($package);

    if(!isset($rexPackage->data[$property]))
      $rexPackage->data[$property] = array();

    $rexPackage->data[$property][$rexPackage->name] = $value;
  }

  /**
   * Gibt eine Eigenschaft des Packages zurück.
   *
   * @param string|array $package Name des Packages
   * @param string $property Name der Eigenschaft
   * @param mixed $default Rückgabewert, falls die Eigenschaft nicht gefunden wurde
   *
   * @return string Wert der Eigenschaft des Packages
   */
  static public function getProperty($package, $property, $default = null)
  {
    $rexPackage = self::create($package);
    return isset($rexPackage->data[$property][$rexPackage->name]) ? $rexPackage->data[$property][$rexPackage->name] : $default;
  }
}