<?php

/**
 * Klasse zum prüfen ob Plugins installiert/aktiviert sind
 * @package redaxo4
 * @version svn:$Id$
 */

class OOPlugin extends rex_addon
{
  /**
   * @override
   * @see redaxo/include/classes/rex_addon#isAvailable($addon)
   */
  /*public static*/ function isAvailable($addon, $plugin)
  {
    return parent::isAvailable(array($addon, $plugin));
  }

  /**
   * @override
   * @see redaxo/include/classes/rex_addon#isActivated($addon)
   */
  /*public static*/ function isActivated($addon, $plugin)
  {
    return parent::isActivated(array($addon, $plugin));
  }

  /**
   * @override
   * @see redaxo/include/classes/rex_addon#isInstalled($addon)
   */
  /*public static*/ function isInstalled($addon, $plugin)
  {
    return parent::isInstalled(array($addon, $plugin));
  }

  /**
   * @override
   * @see redaxo/include/classes/rex_addon#getSupportPage($addon, $default)
   */
  /*public static*/ function getSupportPage($addon, $plugin, $default = null)
  {
    return parent::getSupportPage(array($addon, $plugin), $default);
  }
  
  /**
   * @override
   * @see redaxo/include/classes/rex_addon#getVersion($addon, $default)
   */
  /*public static*/ function getVersion($addon, $plugin, $default = null)
  {
    return parent::getVersion(array($addon, $plugin), $default);
  }
  
  /**
   * @override
   * @see redaxo/include/classes/rex_addon#getAuthor($addon, $default)
   */
  /*public static*/ function getAuthor($addon, $plugin, $default = null)
  {
    return parent::getAuthor(array($addon, $plugin), $default);
  }
  
  /**
   * @override
   * @see redaxo/include/classes/rex_addon#getProperty($addon, $property, $default)
   */
  /*public static*/ function getProperty($addon, $plugin, $property, $default = null)
  {
    return parent::getProperty(array($addon, $plugin), $property, $default);
  }
  
  /**
   * @override
   * @see redaxo/include/classes/rex_addon#setProperty($addon, $property, $value)
   */
  /*public static*/ function setProperty($addon, $plugin, $property, $value)
  {
    return parent::setProperty(array($addon, $plugin), $property, $value);
  }
  
  /**
   * Gibt ein Array aller verfügbaren Plugins zurück.
   * 
   * @param string $addon Name des Addons
   * 
   * @return array Array aller verfügbaren Plugins
   */
  /*public static*/ function getAvailablePlugins($addon)
  {
    $avail = array();
    foreach(OOPlugin::getRegisteredPlugins($addon) as $plugin)
    {
      if(OOPlugin::isAvailable($addon, $plugin))
      {
        $avail[] = $plugin;
      }
    }

    return $avail;
  }
  

  /**
   * Gibt ein Array aller installierten Plugins zurück.
   * 
   * @param string $addon Name des Addons
   * 
   * @return array Array aller registrierten Plugins
   */
  /*public static*/ function getInstalledPlugins($addon)
  {
    $avail = array();
    foreach(OOPlugin::getRegisteredPlugins($addon) as $plugin)
    {
      if(OOPlugin::isInstalled($addon, $plugin))
      {
        $avail[] = $plugin;
      }
    }

    return $avail;
  }

  /**
   * Gibt ein Array aller registrierten Plugins zurück.
   * Ein Plugin ist registriert, wenn es dem System bekannt ist (plugins.inc.php).
   * 
   * @param string $addon Name des Addons
   * 
   * @return array Array aller registrierten Plugins
   */
  /*public static*/ function getRegisteredPlugins($addon)
  {
    global $REX;

    $plugins = array();
    if(isset($REX['ADDON']) && is_array($REX['ADDON']) &&
       isset($REX['ADDON']['plugins']) && is_array($REX['ADDON']['plugins']) &&
       isset($REX['ADDON']['plugins'][$addon]) && is_array($REX['ADDON']['plugins'][$addon]) &&
       isset($REX['ADDON']['plugins'][$addon]['install']) && is_array($REX['ADDON']['plugins'][$addon]['install']))
    {
      $plugins = array_keys($REX['ADDON']['plugins'][$addon]['install']);
    }
    
    return $plugins;
  }
}
