<?php

class rex_addonManager extends rex_baseManager
{
  private $configArray;

  function __construct($configArray)
  {
    $this->configArray = $configArray;
    parent::__construct('addon_');
  }

  public function delete($addonName)
  {
    global $REX;

    // System AddOns dürfen nicht gelöscht werden!
    if(in_array($addonName, $REX['SYSTEM_ADDONS']))
      return $REX['I18N']->msg('addon_systemaddon_delete_not_allowed');

    return parent::delete($addonName);
  }

  public function moveUp($addonName)
  {
    global $REX;

    $key = array_search($addonName, $this->configArray);
    if($key === false)
    {
      throw new rexException('Addon with name "'. $addonName .'" not found!');
    }

    // it's not allowed to move the first addon up
    if($key === 0)
    {
      return $REX['I18N']->msg('addon_move_first_up_not_allowed');
    }

    // swap addon with it's predecessor
    $prev = $this->configArray[$key - 1];
    $this->configArray[$key - 1] = $this->configArray[$key];
    $this->configArray[$key] = $prev;

    // save the changes
    return $this->generateConfig();
  }

  public function moveDown($addonName)
  {
    global $REX;

    $key = array_search($addonName, $this->configArray);
    if($key === false)
    {
      throw new rexException('Addon with name "'. $addonName .'" not found!');
    }

    // it's not allowed to move the last addon down
    if($key === (count($this->configArray) - 1) )
    {
      return $REX['I18N']->msg('addon_move_last_down_not_allowed');
    }

    // swap addon with it's successor
    $next = $this->configArray[$key + 1];
    $this->configArray[$key + 1] = $this->configArray[$key];
    $this->configArray[$key] = $next;

    // save the changes
    return $this->generateConfig();
  }

  protected function includeConfig($addonName, $configFile)
  {
    global $REX; // Nötig damit im Addon verfügbar
    require $configFile;
  }


  protected function includeInstaller($addonName, $installFile)
  {
    global $REX; // Nötig damit im Addon verfügbar
    require $installFile;
  }

  protected function includeUninstaller($addonName, $uninstallFile)
  {
    global $REX; // Nötig damit im Addon verfügbar
    require $uninstallFile;
  }

  protected function generateConfig()
  {
    return rex_generateAddons($this->configArray);
  }

  protected function apiCall($method, array $arguments)
  {
    return rex_call_func(array('rex_ooaddon', $method), $arguments, false);
  }

  protected function loadPackageInfos($addonName)
  {
    return self::loadPackage($addonName);
  }

  protected function baseFolder($addonName)
  {
    return rex_addons_folder($addonName);
  }

  protected function mediaFolder($addonName)
  {
    global $REX;
    return $REX['OPENMEDIAFOLDER'] .DIRECTORY_SEPARATOR .'addons'. DIRECTORY_SEPARATOR .$addonName;
  }

  /**
   * Loads the package.yml into $REX
   *
   * @param string $addonName The name of the addon
   */
  static public function loadPackage($addonName)
  {
    $package_file = rex_addons_folder($addonName). 'package.yml';

    if(is_readable($package_file))
    {
      $ymlConfig = sfYaml::load($package_file);
      if($ymlConfig)
      {
        foreach($ymlConfig as $addonConfig)
        {
          foreach($addonConfig as $confName => $confValue)
          {
            rex_ooaddon::setProperty($addonName, $confName, rex_translate_array($confValue));
          }
        }
      }
    }
  }

  /**
   * Checks if another Addon/Plugin which is activated, depends on the given addon
   *
   * @param string $addonName The name of the addon
   */
  protected function checkDependencies($addonName)
  {
    global $REX;

    $i18nPrefix = 'addon_dependencies_error_';
    $state = array();

    foreach(rex_ooaddon::getAvailableAddons() as $availAddonName)
    {
      $requirements = rex_ooaddon::getProperty($availAddonName, 'requires', array());
      if(isset($requirements['addons']) && is_array($requirements['addons']))
      {
        foreach($requirements['addons'] as $depName => $depAttr)
        {
          if($depName == $addonName)
          {
            $state[] = $REX['I18N']->msg($i18nPrefix .'addon', $availAddonName);
          }
        }
      }

      // check if another Plugin which is installed, depends on the addon being un-installed
      foreach(rex_ooplugin::getAvailablePlugins($availAddonName) as $availPluginName)
      {
        $requirements = rex_ooplugin::getProperty($availAddonName, $availPluginName, 'requires', array());
        if(isset($requirements['addons']) && is_array($requirements['addons']))
        {
          foreach($requirements['addons'] as $depName => $depAttr)
          {
            if($depName == $addonName)
            {
              $state[] = $REX['I18N']->msg($i18nPrefix .'plugin', $availAddonName, $availPluginName);
            }
          }
        }
      }
    }

    return empty($state) ? true : implode('<br />', $state);
  }
}