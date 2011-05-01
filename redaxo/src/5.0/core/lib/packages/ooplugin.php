<?php

/**
 * Klasse zum prüfen ob Plugins installiert/aktiviert sind
 * @package redaxo5
 * @version svn:$Id$
 */

class rex_ooPlugin extends rex_ooPackage
{
  /**
   * Erstellt eine rex_ooPlugin instanz
   *
   * @param string $addon Name des Addons
   */
  protected function __construct($addon, $plugin)
  {
    parent::__construct(array($addon, $plugin));
  }

  /**
   * Prüft, ob ein System-Plugin vorliegt
   *
   * @param string $addon Name des Addons
   * @param string $plugin Name des Plugins
   *
   * @return boolean TRUE, wenn es sich um ein System-Addon handelt, sonst FALSE
   */
  static public function isSystemPlugin($addon, $plugin)
  {
    global $REX;
    return in_array(array($addon, $plugin), $REX['SYSTEM_PACKAGES']);
  }

  /**
   * @override
   * @see rex_package::isAvailable()
   */
  static public function isAvailable($addon, $plugin = null)
  {
    if ($plugin === null)
    {
      throw new InvalidArgumentException('Missing Argument 2 for rex_ooPlugin::isAvailable()');
    }
    return parent::isAvailable($addon) && parent::isAvailable(array($addon, $plugin));
  }

  /**
   * @override
   * @seerex_package::isActivated()
   */
  static public function isActivated($addon, $plugin = null)
  {
    if ($plugin === null)
    {
      throw new InvalidArgumentException('Missing Argument 2 for rex_ooPlugin::isActivated()');
    }
    return parent::isActivated($addon) && parent::isActivated(array($addon, $plugin));
  }

  /**
   * @override
   * @see rex_package::isInstalled()
   */
  static public function isInstalled($addon, $plugin = null)
  {
    if ($plugin === null)
    {
      throw new InvalidArgumentException('Missing Argument 2 for rex_ooPlugin::isInstalled()');
    }
    return parent::isInstalled($addon) && parent::isInstalled(array($addon, $plugin));
  }

  /**
   * @override
   * @see rex_package::getSupportPage()
   */
  static public function getSupportPage($addon, $plugin = null, $default = null)
  {
    if ($plugin === null)
    {
      throw new InvalidArgumentException('Missing Argument 2 for rex_ooPlugin::getSupportPage()');
    }
    return parent::getSupportPage(array($addon, $plugin), $default);
  }

  /**
   * @override
   * @see rex_package::getVersion()
   */
  static public function getVersion($addon, $plugin = null, $default = null)
  {
    if ($plugin === null)
    {
      throw new InvalidArgumentException('Missing Argument 2 for rex_ooPlugin::getVersion()');
    }
    return parent::getVersion(array($addon, $plugin), $default);
  }

  /**
   * @override
   * @see rex_package::getAuthor()
   */
  static public function getAuthor($addon, $plugin = null, $default = null)
  {
    if ($plugin === null)
    {
      throw new InvalidArgumentException('Missing Argument 2 for rex_ooPlugin::getAuthor()');
    }
    return parent::getAuthor(array($addon, $plugin), $default);
  }

  /**
   * @override
   * @see rex_package::getProperty()
   */
  static public function getProperty($addon, $plugin, $property = null, $default = null)
  {
    if ($property === null)
    {
      throw new InvalidArgumentException('Missing Argument 3 for rex_ooPlugin::getProperty()');
    }
    return parent::getProperty(array($addon, $plugin), $property, $default);
  }

  /**
   * @override
   * @see rex_package::setProperty()
   */
  static public function setProperty($addon, $plugin, $property, $value = null)
  {
    if ($value === null)
    {
      throw new InvalidArgumentException('Missing Argument 4 for rex_ooPlugin::setProperty()');
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
    foreach(rex_ooPlugin::getRegisteredPlugins($addon) as $plugin)
    {
      if(rex_ooPlugin::isAvailable($addon, $plugin))
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
    foreach(rex_ooPlugin::getRegisteredPlugins($addon) as $plugin)
    {
      if(rex_ooPlugin::isInstalled($addon, $plugin))
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
