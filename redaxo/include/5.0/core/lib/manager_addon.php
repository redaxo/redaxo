<?php

class rex_addonManager extends rex_baseManager
{
  var $configArray;
  
  function __construct($configArray)
  {
    $this->configArray = $configArray;
    parent::__construct('addon_');
  }
  
  public function delete($addonName)
  {
    global $REX, $I18N;
    
    // System AddOns dürfen nicht gelöscht werden!
    if(in_array($addonName, $REX['SYSTEM_ADDONS']))
      return $I18N->msg('addon_systemaddon_delete_not_allowed');
      
    return parent::delete($addonName);
  }
  
  public function moveUp($addonName)
  {
    global $I18N;
    
    $key = array_search($addonName, $this->configArray);
    if($key === false)
    {
      throw new rexException('Addon with name "'. $addonName .'" not found!');
    }
    
    // it's not allowed to move the first addon up
    if($key === 0)
    {
      return $I18N->msg('addon_move_first_up_not_allowed');
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
    global $I18N;
    
    $key = array_search($addonName, $this->configArray);
    if($key === false)
    {
      throw new rexException('Addon with name "'. $addonName .'" not found!');
    }

    // it's not allowed to move the last addon down
    if($key === (count($this->configArray) - 1) )
    {
      return $I18N->msg('addon_move_last_down_not_allowed');
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
    global $REX, $I18N; // Nötig damit im Addon verfügbar
    require $configFile;
  }
  
  
  protected function includeInstaller($addonName, $installFile)
  {
    global $REX, $I18N; // Nötig damit im Addon verfügbar
    require $installFile;
  }
  
  protected function includeUninstaller($addonName, $uninstallFile)
  {
    global $REX, $I18N; // Nötig damit im Addon verfügbar
    require $uninstallFile;
  }
  
  protected function generateConfig()
  {
    return rex_generateAddons($this->configArray);
  }
  
  protected function apiCall($method, $arguments)
  {
    if(!is_array($arguments))
      trigger_error('Expecting $arguments to be an array!', E_USER_ERROR);
      
    return rex_call_func(array('OOAddon', $method), $arguments, false);
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
            OOAddon::setProperty($addonName, $confName, rex_translate_array($confValue));
          }
        }
      }
    }
  }  
}