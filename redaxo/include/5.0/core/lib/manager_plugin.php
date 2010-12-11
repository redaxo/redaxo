<?php

class rex_pluginManager extends rex_baseManager
{
  var $configArray;
  var $addonName;
  
  function __construct($configArray, $addonName)
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
    global $REX, $I18N; // Nötig damit im Addon verfügbar
        
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
    global $I18N;
    
    $key = array_search($pluginName, $this->configArray[$this->addonName]);
    if($key === false)
    {
      throw new rexException('Plugin with name "'. $pluginName .'" not found!');
    }
    
    // it's not allowed to move the first addon up
    if($key === 0)
    {
      return $I18N->msg('addon_move_first_up_not_allowed');
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
    global $I18N;
    
    $key = array_search($pluginName, $this->configArray[$this->addonName]);
    if($key === false)
    {
      throw new rexException('Plugin with name "'. $pluginName .'" not found!');
    }

    // it's not allowed to move the last addon down
    if($key === (count($this->configArray[$this->addonName]) - 1) )
    {
      return $I18N->msg('addon_move_last_down_not_allowed');
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
  
  protected function apiCall($method, $arguments)
  {
    if(!is_array($arguments))
      trigger_error('Expecting $arguments to be an array!', E_USER_ERROR);
      
    // addonName als 1. Parameter einfügen
    array_unshift($arguments, $this->addonName);
      
    return rex_call_func(array('OOPlugin', $method), $arguments, false);
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
            OOPlugin::setProperty($addonName, $pluginName, $confName, $confValue);
          }
        }
      }
    }
  }    
}