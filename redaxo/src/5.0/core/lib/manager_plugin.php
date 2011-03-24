<?php

class rex_pluginManager extends rex_packageManager
{
  private $addonName;

  function __construct($addonName)
  {
    $this->addonName = $addonName;
    parent::__construct('plugin_');
  }

  public function delete($pluginName)
  {
    global $REX;

    // System AddOns dürfen nicht gelöscht werden!
    if(in_array(array($this->addonName, $pluginName), $REX['SYSTEM_PACKAGES']))
      return $REX['I18N']->msg('plugin_systemplugin_delete_not_allowed');

    return parent::delete($pluginName);
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
    return rex_path::plugin($this->addonName, $pluginName);
  }

  protected function assetsFolder($pluginName)
  {
    return rex_path::pluginAssets($this->addonName, $pluginName);
  }

  protected function dataFolder($pluginName)
  {
    return rex_path::pluginData($this->addonName, $pluginName);
  }

  protected function package($pluginName)
  {
    return array($this->addonName, $pluginName);
  }

  protected function configNamespace($pluginName)
  {
    return $this->addonName .'/'. $pluginName;
  }

  /**
   * Loads the package.yml into $REX
   *
   * @param string $addonName The name of the addon
   */
  static public function loadPackage($addonName, $pluginName)
  {
    $package_file = rex_path::plugin($addonName, $pluginName, 'package.yml');

    if(is_readable($package_file))
    {
      $ymlConfig = rex_file::getConfig($package_file);
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