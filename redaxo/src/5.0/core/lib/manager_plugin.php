<?php

class rex_pluginManager extends rex_baseManager
{
  private
    $configArray,
    $addonName;

  function __construct(array $configArray, $addonName)
  {
    $this->configArray =& $configArray;
    $this->addonName = $addonName;
    parent::__construct('plugin_');
  }

  /**
   * Wandelt ein AddOn in ein PlugIn eines anderen AddOns um
   *
   * @param $addonName AddOn dem das PlugIn eingefügt werden soll
   * @param $pluginName Name des Plugins
   * @param $includeFile Datei die eingebunden und umgewandelt werden soll
   */
  static public function addon2plugin($addonName, $pluginName, $includeFile)
  {
    global $REX; // Nötig damit im Addon verfügbar

    $ADDONSsic = $REX['ADDON'];
    $REX['ADDON'] = array();

    require $includeFile;

    $plugInConfig = array();
    if(isset($ADDONSsic['plugins'][$addonName]))
    {
      $plugInConfig = $ADDONSsic['plugins'][$addonName];
    }

    if(isset($REX['ADDON']) && is_array($REX['ADDON']))
    {
      foreach(array_keys($REX['ADDON']) as $key)
      {
        // Alle Eigenschaften die das PlugIn betreffen verschieben
        if(isset($REX['ADDON'][$key][$pluginName]))
        {
          $plugInConfig[$key][$pluginName] = $REX['ADDON'][$key][$pluginName];
          unset($REX['ADDON'][$key][$pluginName]);

          // ggf array das leer geworden ist löschen
          // damit es beim merge später nicht ein vorhandes überschreibt
          if(empty($REX['ADDON'][$key]))
          {
            unset($REX['ADDON'][$key]);
          }
        }
      }
    }

    // Addoneinstellungen als PlugIndaten speichern
    $ADDONSsic['plugins'][$addonName] = $plugInConfig;
    // Alle überbleibenden Keys die ggf. andere Addons beinflussen einfließen lassen
    $REX['ADDON'] = array_merge_recursive($ADDONSsic, $REX['ADDON']);
  }

  public function moveUp($pluginName)
  {
    global $REX;

    $key = array_search($pluginName, $this->configArray[$this->addonName]);
    if($key === false)
    {
      throw new rexException('Plugin with name "'. $pluginName .'" not found!');
    }

    // it's not allowed to move the first addon up
    if($key === 0)
    {
      return $REX['I18N']->msg('addon_move_first_up_not_allowed');
    }

    // swap addon with it's predecessor
    $prev = $this->configArray[$this->addonName][$key - 1];
    $this->configArray[$this->addonName][$key - 1] = $this->configArray[$this->addonName][$key];
    $this->configArray[$this->addonName][$key] = $prev;

    // save the changes
    return $this->generateConfig();
  }

  public function moveDown($pluginName)
  {
    global $REX;

    $key = array_search($pluginName, $this->configArray[$this->addonName]);
    if($key === false)
    {
      throw new rexException('Plugin with name "'. $pluginName .'" not found!');
    }

    // it's not allowed to move the last addon down
    if($key === (count($this->configArray[$this->addonName]) - 1) )
    {
      return $REX['I18N']->msg('addon_move_last_down_not_allowed');
    }

    // swap addon with it's successor
    $next = $this->configArray[$this->addonName][$key + 1];
    $this->configArray[$this->addonName][$key + 1] = $this->configArray[$this->addonName][$key];
    $this->configArray[$this->addonName][$key] = $next;

    // save the changes
    return $this->generateConfig();
  }

  protected function includeConfig($addonName, $configFile)
  {
    rex_pluginManager::addon2plugin($this->addonName, $addonName, $configFile);
  }

  protected function includeInstaller($addonName, $installFile)
  {
    rex_pluginManager::addon2plugin($this->addonName, $addonName, $installFile);
  }

  protected function includeUninstaller($addonName, $uninstallFile)
  {
    rex_pluginManager::addon2plugin($this->addonName, $addonName, $uninstallFile);
  }

  protected function generateConfig()
  {
    return rex_generatePlugins($this->configArray);
  }

  protected function apiCall($method, array $arguments)
  {
    // addonName als 1. Parameter einfügen
    array_unshift($arguments, $this->addonName);

    return rex_call_func(array('rex_ooPlugin', $method), $arguments, false);
  }

  protected function loadPackageInfos($pluginName)
  {
    return self::loadPackage($this->addonName, $pluginName);
  }

  protected function baseFolder($pluginName)
  {
    return rex_plugins_folder($this->addonName, $pluginName);
  }

  protected function mediaFolder($pluginName)
  {
    global $REX;
    return $REX['OPENMEDIAFOLDER'] .DIRECTORY_SEPARATOR .'addons'. DIRECTORY_SEPARATOR. $this->addonName .DIRECTORY_SEPARATOR .'plugins'. DIRECTORY_SEPARATOR. $pluginName;
  }

  /**
   * Loads the package.yml into $REX
   *
   * @param string $addonName The name of the addon
   */
  static public function loadPackage($addonName, $pluginName)
  {
    $package_file = rex_plugins_folder($addonName, $pluginName). 'package.yml';

    if(is_readable($package_file))
    {
      $ymlConfig = sfYaml::load($package_file);
      if($ymlConfig)
      {
        foreach($ymlConfig as $addonConfig)
        {
          foreach($addonConfig as $confName => $confValue)
          {
            rex_ooPlugin::setProperty($addonName, $pluginName, $confName, rex_translate_array($confValue));
          }
        }
      }
    }
  }

  /**
   * Checks if another Addon/Plugin which is activated, depends on the given plugin
   *
   * @param string $pluginName The name of the plugin
   */
  protected function checkDependencies($pluginName)
  {
    global $REX;

    $i18nPrefix = 'addon_dependencies_error_';
    $state = array();

    foreach(rex_ooAddon::getAvailableAddons() as $availAddonName)
    {
      $requirements = rex_ooAddon::getProperty($availAddonName, 'requires', array());
      if(isset($requirements['addons']) && is_array($requirements['addons']))
      {
        foreach($requirements['addons'] as $addonName => $addonAttr)
        {
          if($addonName == $this->addonName && isset($addonAttr['plugins']) && is_array($addonAttr['plugins']))
          {
            foreach($addonAttr['plugins'] as $depName => $depAttr)
            {
              if($depName == $pluginName)
              {
                $state[] = $REX['I18N']->msg($i18nPrefix .'addon', $availAddonName);
              }
            }
          }
        }
      }

      // check if another Plugin which is installed, depends on the addon being un-installed
      foreach(rex_ooPlugin::getAvailablePlugins($availAddonName) as $availPluginName)
      {
        $requirements = rex_ooPlugin::getProperty($availAddonName, $availPluginName, 'requires', array());
        if(isset($requirements['addons']) && is_array($requirements['addons']))
        {
          foreach($requirements['addons'] as $addonName => $addonAttr)
          {
            if($addonName == $this->addonName && isset($addonAttr['plugins']) && is_array($addonAttr['plugins']))
            {
              foreach($addonAttr['plugins'] as $depName => $depAttr)
              {
                if($depName == $pluginName)
                {
                  $state[] = $REX['I18N']->msg($i18nPrefix .'plugin', $availAddonName, $availPluginName);
                }
              }
            }
          }
        }
      }
    }

    return empty($state) ? true : implode('<br />', $state);
  }
}