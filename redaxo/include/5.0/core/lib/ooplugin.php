<?php

/**
 * Klasse zum prüfen ob Plugins installiert/aktiviert sind
 * @package redaxo4
 * @version svn:$Id$
 */

class OOPlugin extends rex_addon
{
  /**
   * Erstellt eine OOPlugin instanz
   * 
   * @param string $addon Name des Addons
   */
  protected function __construct($addon, $plugin)
  {
    parent::__construct(array($addon, $plugin));
  }
  
  /**
   * @override
   * @see redaxo/include/classes/rex_addon#isAvailable($addon)
   */
  static public function isAvailable($addon, $plugin = null)
  {
    if ($plugin === null)
    {
      throw new InvalidArgumentException('Missing Argument 2 for OOPlugin::isAvailable()');
    }
    return parent::isAvailable(array($addon, $plugin));
  }

  /**
   * @override
   * @see redaxo/include/classes/rex_addon#isActivated($addon)
   */
  static public function isActivated($addon, $plugin = null)
  {
    if ($plugin === null)
    {
      throw new InvalidArgumentException('Missing Argument 2 for OOPlugin::isActivated()');
    }
    return parent::isActivated(array($addon, $plugin));
  }

  /**
   * @override
   * @see redaxo/include/classes/rex_addon#isInstalled($addon)
   */
  static public function isInstalled($addon, $plugin = null)
  {
    if ($plugin === null)
    {
      throw new InvalidArgumentException('Missing Argument 2 for OOPlugin::isInstalled()');
    }
    return parent::isInstalled(array($addon, $plugin));
  }

  /**
   * @override
   * @see redaxo/include/classes/rex_addon#getSupportPage($addon, $default)
   */
  static public function getSupportPage($addon, $plugin = null, $default = null)
  {
    if ($plugin === null)
    {
      throw new InvalidArgumentException('Missing Argument 2 for OOPlugin::getSupportPage()');
    }
    return parent::getSupportPage(array($addon, $plugin), $default);
  }
  
  /**
   * @override
   * @see redaxo/include/classes/rex_addon#getVersion($addon, $default)
   */
  static public function getVersion($addon, $plugin = null, $default = null)
  {
    if ($plugin === null)
    {
      throw new InvalidArgumentException('Missing Argument 2 for OOPlugin::getVersion()');
    }
    return parent::getVersion(array($addon, $plugin), $default);
  }
  
  /**
   * @override
   * @see redaxo/include/classes/rex_addon#getAuthor($addon, $default)
   */
  static public function getAuthor($addon, $plugin = null, $default = null)
  {
    if ($plugin === null)
    {
      throw new InvalidArgumentException('Missing Argument 2 for OOPlugin::getAuthor()');
    }
    return parent::getAuthor(array($addon, $plugin), $default);
  }
  
  /**
   * @override
   * @see redaxo/include/classes/rex_addon#getProperty($addon, $property, $default)
   */
  static public function getProperty($addon, $plugin, $property = null, $default = null)
  {
    if ($property === null)
    {
      throw new InvalidArgumentException('Missing Argument 3 for OOPlugin::getProperty()');
    }
    return parent::getProperty(array($addon, $plugin), $property, $default);
  }
  
  /**
   * @override
   * @see redaxo/include/classes/rex_addon#setProperty($addon, $property, $value)
   */
  static public function setProperty($addon, $plugin, $property, $value = null)
  {
    if ($value === null)
    {
      throw new InvalidArgumentException('Missing Argument 4 for OOPlugin::setProperty()');
    }
    return parent::setProperty(array($addon, $plugin), $property, $value);
  }
  
  /**
   * Gibt ein Array aller verfügbaren Plugins zurück für das übergebene Addon zurück.
   * 
   * @param string $addon Name des Addons
   * 
   * @return array Array aller verfügbaren Plugins
   */
  static public function getAvailablePlugins($addon)
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
   * Gibt ein Array aller installierten Plugins zurück für das übergebene Addon zurück.
   * 
   * @param string $addon Name des Addons
   * 
   * @return array Array aller registrierten Plugins
   */
  static public function getInstalledPlugins($addon)
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
   * Gibt ein Array aller registrierten Plugins zurück für das übergebene Addon zurück.
   * Ein Plugin ist registriert, wenn es dem System bekannt ist (plugins.inc.php).
   * 
   * @param string $addon Name des Addons
   * 
   * @return array Array aller registrierten Plugins
   */
  static public function getRegisteredPlugins($addon)
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
