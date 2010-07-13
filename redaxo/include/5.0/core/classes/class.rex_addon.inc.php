<?php

/**
 * Basisklasse für Addons/Plugins
 * 
 * @package redaxo4
 * @version svn:$Id$
 */

/*private abstract*/ class rex_addon
{
  /*static array*/ var $data;
  /*private mixed*/ var $name;
  
  /**
   * Privater rex_addon Konstruktor.
   * Erstellen von Objekten dieser Klasse ist nicht erlaubt!
   * 
   * @param string|array $namespace Namensraum des rex-Addons 
   */
  /*private*/ function rex_addon($namespace)
  {
    global $REX;
    
    // plugin?
    if(is_array($namespace))
    {
      if(!isset($namespace[0]) || !isset($namespace[1]) ||
         !is_string($namespace[0]) || !is_string($namespace[1]))
      {
        trigger_error('Unexpected namespace format!', E_USER_ERROR);
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
  /*protected static*/ function create($namespace)
  {
    static $addons = array();
    
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
  
  /**
   * Prüft ob das rex-Addon verfügbar ist, also installiert und aktiviert.
   * 
   * @param string|array $addon Name des Addons
   * 
   * @return boolean TRUE, wenn das rex-Addon verfügbar ist, sonst FALSE
   */
  /*public static*/ function isAvailable($addon)
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
  /*public static*/ function isActivated($addon)
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
  /*public static*/ function isInstalled($addon)
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
  /*public static*/ function getVersion($addon, $default = null)
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
  /*public static*/ function getAuthor($addon, $default = null)
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
  /*public static*/ function getSupportPage($addon, $default = null)
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
  /*public static*/ function setProperty($addon, $property, $value)
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
  /*public static*/ function getProperty($addon, $property, $default = null)
  {
    $rexAddon = rex_addon::create($addon);
    return isset($rexAddon->data[$property][$rexAddon->name]) ? $rexAddon->data[$property][$rexAddon->name] : $default;
  }
}