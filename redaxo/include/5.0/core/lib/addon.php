<?php

/**
 * Basisklasse für Addons/Plugins
 * 
 * @package redaxo4
 * @version svn:$Id$
 */
class rex_addon
{
  /*array*/ var $data;
  private $name;
  
  /**
   * rex_addon Konstruktor.
   * 
   * @param string|array $namespace Namensraum des rex-Addons 
   */
  protected function __construct($namespace)
  {
    global $REX;
    
    // plugin?
    if(is_array($namespace))
    {
      if(!isset($namespace[0]) || !isset($namespace[1]) ||
         !is_string($namespace[0]) || !is_string($namespace[1]))
      {
        throw new rexException('Unexpected namespace format!');
      }
        
      $addon = $namespace[0];
      $plugin = $namespace[1];
      $this->data =& $REX['ADDON']['plugins'][$addon];
      $this->name = $plugin;
    }
    // addon?
    else
    {
      $this->data =& $REX['ADDON'];
      $this->name = $namespace;
    }
  }
  
  /**
   * Erstellt ein rex-Addon aus dem Namespace $namespace.
   * 
   * @param string|array $namespace Namensraum des rex-Addons
   *  
   * @return rex_addon Zum namespace erstellte rex-Addon instanz
   */
  protected function create($namespace)
  {
    // called from a static context? (rex <= 4.3.x)
    if($namespace != null)
    {
      $addons = array();
      
      $nsString = $namespace;
      if(is_array($namespace))
      {
        $nsString = implode('/', $namespace);
      }
      
      if(!isset($addons[$nsString]))
      {
        $addons[$nsString] = new rex_addon($namespace); 
      }
      
      return $addons[$nsString];
    }
    // called from usual context? (rex >= 5.x)
    else if (isset($this))
    {
      return $this;
    }
    else
    {
      throw new rexException('Unexpected state!');
    }
  }
  
  /**
   * Prüft ob das rex-Addon verfügbar ist, also installiert und aktiviert.
   * 
   * @param string|array $addon Name des Addons
   * 
   * @return boolean TRUE, wenn das rex-Addon verfügbar ist, sonst FALSE
   */
  static public function isAvailable($addon)
  {
    return rex_addon::isInstalled($addon) && rex_addon::isActivated($addon);
  }

  /**
   * Prüft ob das rex-Addon aktiviert ist.
   * 
   * @param string|array $addon Name des Addons
   * 
   * @return boolean TRUE, wenn das rex-Addon aktiviert ist, sonst FALSE
   */
  static public function isActivated($addon)
  {
    return rex_addon::getProperty($addon, 'status', false) == true;
  }
  
  /**
   * Prüft ob das rex-Addon installiert ist.
   * 
   * @param string|array $addon Name des Addons
   * 
   * @return boolean TRUE, wenn das rex-Addon installiert ist, sonst FALSE
   */
  static public function isInstalled($addon)
  {
    return rex_addon::getProperty($addon, 'install', false) == true;
  }

  /**
   * Gibt die Version des rex-Addons zurück.
   * 
   * @param string|array $addon Name des Addons
   * @param mixed $default Rückgabewert, falls keine Version gefunden wurde
   * 
   * @return string Versionsnummer des Addons
   */
  static public function getVersion($addon, $default = null)
  {
    return rex_addon::getProperty($addon, 'version', $default);
  }

  /**
   * Gibt den Autor des rex-Addons zurück.
   * 
   * @param string|array $addon Name des Addons
   * @param mixed $default Rückgabewert, falls kein Autor gefunden wurde
   * 
   * @return string Autor des Addons
   */
  static public function getAuthor($addon=null, $default = null)
  {
    return rex_addon::getProperty($addon, 'author', $default);
  }

  /**
   * Gibt die Support-Adresse des rex-Addons zurück.
   * 
   * @param string|array $addon Name des Addons
   * @param mixed $default Rückgabewert, falls keine Support-Adresse gefunden wurde
   * 
   * @return string Versionsnummer des Addons
   */
  static public function getSupportPage($addon=null, $default = null)
  {
    return rex_addon::getProperty($addon, 'supportpage', $default);
  }

  /**
   * Setzt eine Eigenschaft des rex-Addons.
   * 
   * @param string|array $addon Name des Addons
   * @param string $property Name der Eigenschaft 
   * @param mixed $property Wert der Eigenschaft 
   * 
   * @return string Versionsnummer des Addons
   */
  static public function setProperty($addon, $property, $value)
  {
    $rexAddon = rex_addon::create($addon);
    
    if(!isset($rexAddon->data[$property]))
      $rexAddon->data[$property] = array();

    $rexAddon->data[$property][$rexAddon->name] = $value;
  }

  /**
   * Gibt eine Eigenschaft des rex-Addons zurück.
   * 
   * @param string|array $addon Name des Addons
   * @param string $property Name der Eigenschaft 
   * @param mixed $default Rückgabewert, falls die Eigenschaft nicht gefunden wurde
   * 
   * @return string Wert der Eigenschaft des Addons
   */
  static public function getProperty($addon, $property, $default = null)
  {
    $rexAddon = rex_addon::create($addon);
    return isset($rexAddon->data[$property][$rexAddon->name]) ? $rexAddon->data[$property][$rexAddon->name] : $default;
  }
}