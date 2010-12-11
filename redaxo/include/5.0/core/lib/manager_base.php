<?php

/**
 * Managerklasse zum handeln von rexAddons
 */
abstract class rex_baseManager
{
  var $i18nPrefix;
  
  /**
   * Konstruktor
   * 
   * @param $i18nPrefix Sprachprefix aller I18N Sprachschlüssel
   */
  function __construct($i18nPrefix)
  {
    $this->i18nPrefix = $i18nPrefix;
  }
  
  /**
   * Installiert ein Addon
   * 
   * @param $addonName Name des Addons
   * @param $installDump Flag, ob die Datei install.sql importiert werden soll
   */
  public function install($addonName, $installDump = TRUE)
  {
  	global $REX;
  	
    $state = TRUE;
  
    $install_dir  = $this->baseFolder($addonName);
    $install_file = $install_dir.'install.inc.php';
    $install_sql  = $install_dir.'install.sql';
    $config_file  = $install_dir.'config.inc.php';
    $files_dir    = $install_dir.'files';
    $package_file = $install_dir.'package.yml';
    
    // Pruefen des Addon Ornders auf Schreibrechte,
    // damit das Addon spaeter wieder geloescht werden kann
    $state = rex_is_writable($install_dir);
    
    if ($state === TRUE)
    {
      // load package infos
      static::loadPackageInfos($addonName);
  
      // check if dependencies are satisfied
      $dependencies = $this->apiCall('getProperty', array($addonName, 'dependencies', array()));
      $state = self::checkDependencies($dependencies);

      if($state === TRUE)
      {
        if (is_readable($install_file))
        {
          $this->includeInstaller($addonName, $install_file);
          
          $state = $this->verifyInstallation($addonName);
          if($state === TRUE)
          {
            // TODO: Warum laden wir die config bei der Installation?!
            $state = $this->loadConfig($addonName, $config_file);
    
            if($installDump === TRUE && $state === TRUE && is_readable($install_sql))
            {
              $state = rex_install_dump($install_sql);
    
              if($state !== TRUE)
                $state = 'Error found in install.sql:<br />'. $state;
            }
    
            // Installation ok
            if ($state === TRUE)
            {
              // regenerate Addons file
              $state = $this->generateConfig();
            }
          }
        }
        else
        {
          $state = $this->I18N('install_not_found');
        }
      }
    }
  
    // Dateien kopieren
    if($state === TRUE && is_dir($files_dir))
    {
      if(!rex_copyDir($files_dir, $this->mediaFolder($addonName), $REX['OPENMEDIAFOLDER']))
      {
        $state = $this->I18N('install_cant_copy_files');
      }
    }
    
    if($state !== TRUE)
      $this->apiCall('setProperty', array($addonName, 'install', 0));
  
    return $state;
  }
  
  /**
   * Loads the configuration of the Addon $addonName into $REX
   * 
   * @param string $addonName The name of the addon
   * @param string $config_file The path to the config file
   */
  private function loadConfig($addonName, $config_file)
  {
    $state = TRUE;
    
    // check if config file exists
    if (is_readable($config_file))
    {
      if (!$this->apiCall('isActivated', array($addonName)))
      {
        $this->includeConfig($addonName, $config_file);
      }
    }
    else
    {
      $state = $this->I18N('config_not_found');
    }

    return $state;
  }
  
  /**
   * Verifies if the installation of the given Addon was successfull.
   * 
   * @param string $addonName The name of the addon
   */
  private function verifyInstallation($addonName)
  {
    $state = TRUE;
  
    // Wurde das "install" Flag gesetzt?
    // Fehlermeldung ausgegeben? Wenn ja, Abbruch
    $instmsg = $this->apiCall('getProperty', array($addonName, 'installmsg', ''));
    
    if (!$this->apiCall('isInstalled', array($addonName)) || $instmsg)
    {
      $state = $this->I18N('no_install', $addonName).'<br />';
      if ($instmsg == '')
      {
        $state .= $this->I18N('no_reason');
      }
      else
      {
        $state .= $instmsg;
      }
    }
    
    return $state;    
  }
  
  /**
   * Verifies if the un-installation of the given Addon was successfull.
   * 
   * @param string $addonName The name of the addon
   */
  private function verifyUninstallation($addonName)
  {
    $state = TRUE;
    
    // Wurde das "install" Flag gesetzt?
    // Fehlermeldung ausgegeben? Wenn ja, Abbruch
    $instmsg = $this->apiCall('getProperty', array($addonName, 'installmsg', ''));
    
    if ($this->apiCall('isInstalled', array($addonName)) || $instmsg)
    {
      $state = $this->I18N('no_uninstall', $addonName).'<br />';
      if ($instmsg == '')
      {
        $state .= $this->I18N('no_reason');
      }
      else
      {
        $state .= $instmsg;
      }
    }
    
    return $state;
  }

  /**
   * Checks whether the given dependencies are satisfied.
   * 
   * @param array $dependencies
   * @throws InvalidArgumentException
   */
  static private function checkDependencies(array $dependencies)
  {
    $state = TRUE;
    
    foreach($dependencies as $depName => $depAttr)
    {
      // check if dependency exists
      if(!OOAddon::isAvailable($depName))
      {
        $state = 'Missing required Addon "'. $depName .'"!';
        break;
      }
      
      // check dependency exact-version
      if(isset($depAttr['version']) && OOAddon::getProperty($depName, 'version') != $depAttr['version'])
      {
        $state = 'Required Addon "'. $depName . '" not in required version '. $depAttr['version'] . ' (found: '. OOAddon::getProperty($depName, 'version') .')';
        break;
      }
      else
      {
        // check dependency min-version
        if(isset($depAttr['min-version']) && OOAddon::getProperty($depName, 'version') < $depAttr['min-version'])
        {
          $state = 'Required Addon "'. $depName . '" not in required version! Requires at least  '. $depAttr['min-version'] . ', but found: '. OOAddon::getProperty($depName, 'version') .'!';
          break;
        }
        // check dependency min-version
        if(isset($depAttr['max-version']) && OOAddon::getProperty($depName, 'version') > $depAttr['max-version'])
        {
          $state = 'Required Addon "'. $depName . '" not in required version! Requires at most  '. $depAttr['max-version'] . ', but found: '. OOAddon::getProperty($depName, 'version') .'!';
          break;
        }
      }
    }
    
    return $state;
  }
  
  /**
   * De-installiert ein Addon
   * 
   * @param $addonName Name des Addons
   */
  public function uninstall($addonName)
  {
    $state = TRUE;
    
    $install_dir    = $this->baseFolder($addonName);
    $uninstall_file = $install_dir.'uninstall.inc.php';
    $uninstall_sql  = $install_dir.'uninstall.sql';
    $package_file   = $install_dir.'package.yml';
  
    if (is_readable($uninstall_file))
    {
      // check if another Addon which is installed, depends on the addon being un-installed
      foreach(OOAddon::getAvailableAddons() as $availAddonName)
      {
        $dependencies = OOAddon::getProperty($availAddonName, 'dependencies', array());
        foreach($dependencies as $depName => $depAttr)
        {
          if($depName == $addonName)
          {
            $state = 'Addon "'. $addonName .'" is required by installed Addon "'. $availAddonName .'"!';
            break 2;
          }
        }
        
        // check if another Plugin which is installed, depends on the addon being un-installed
        foreach(OOPlugin::getAvailablePlugins($availAddonName) as $availPluginName)
        {
          $dependencies = OOPlugin::getProperty($availAddonName, $availPluginName, 'dependencies', array());
          foreach($dependencies as $depName => $depAttr)
          {
            if($depName == $addonName)
            {
              $state = 'Addon "'. $addonName .'" is required by installed Plugin "'. $availPluginName .'" of Addon "'. $availAddonName .'"!';
              break 3;
            }
          }
        }
      }
      
      // start un-installation
      if($state === TRUE)
      {
        $this->includeUninstaller($addonName, $uninstall_file);
        $state = $this->verifyUninstallation($addonName);
      }

      if($state === TRUE)
      {
        $state = $this->deactivate($addonName);
  
        if($state === TRUE && is_readable($uninstall_sql))
        {
          $state = rex_install_dump($uninstall_sql);
  
          if($state !== TRUE)
            $state = 'Error found in uninstall.sql:<br />'. $state;
        }
  
        if ($state === TRUE)
        {
          // regenerate Addons file
          $state = $this->generateConfig();
        }
      }
    }
    else
    {
      $state = $this->I18N('uninstall_not_found');
    }
    
    $mediaFolder = $this->mediaFolder($addonName);
    if($state === TRUE && is_dir($mediaFolder))
    {
      if(!rex_deleteDir($mediaFolder, TRUE))
      {
        $state = $this->I18N('install_cant_delete_files');
      }
    }
  
    // Fehler beim uninstall -> Addon bleibt installiert
    if($state !== TRUE)
      $this->apiCall('setProperty', array($addonName, 'install', 1));
  
    return $state;
  }
  
  /**
   * Aktiviert ein Addon
   * 
   * @param $addonName Name des Addons
   */
  public function activate($addonName)
  {
    if ($this->apiCall('isInstalled', array($addonName)))
    {
      $this->apiCall('setProperty', array($addonName, 'status', 1));
      $state = $this->generateConfig();
    }
    else
    {
      $state = $this->I18N('no_activation', $addonName);
    }
  
    // error while config generation, rollback addon status
    if($state !== TRUE)
      $this->apiCall('setProperty', array($addonName, 'status', 0));

    return $state;
  }
  
  /**
   * Deaktiviert ein Addon
   * 
   * @param $addonName Name des Addons
   */
  public function deactivate($addonName)
  {
    $this->apiCall('setProperty', array($addonName, 'status', 0));
    $state = $this->generateConfig();

    // error while config generation, rollback addon status
    if($state !== TRUE)
      $this->apiCall('setProperty', array($addonName, 'status', 1));
      
    // reload autoload cache when addon is deactivated,
    // so the index doesn't contain outdated class definitions
    if($state === TRUE) 
      rex_autoload::getInstance()->removeCache();
      
    return $state;
  }
  
  /**
   * Löscht ein Addon im Filesystem
   * 
   * @param $addonName Name des Addons
   */
  public function delete($addonName)
  {
    // zuerst deinstallieren
    // bei erfolg, komplett löschen
    $state = TRUE;
    $state = $state && $this->uninstall($addonName);
    $state = $state && rex_deleteDir($this->baseFolder($addonName), TRUE);
    $state = $state && $this->generateConfig();
  
    return $state;
  }
  
  /**
   * Moves the addon one step forward in the include-chain.
   * The addon will therefore be included earlier in the bootstrap process.
   * 
   * @param $addonName Name of the addon
   */
  public abstract function moveUp($addonName);
  
  /**
   * Moves the addon one step backwards in the include-chain.
   * The addon will therefore be included later in the bootstrap process.
   * 
   * @param $addonName Name of the addon
   */
  public abstract function moveDown($addonName);
  
  /**
   * Übersetzen eines Sprachschlüssels unter Verwendung des Prefixes 
   */
  protected function I18N()
  {
    global $I18N;
    
    $args = func_get_args();
    $args[0] = $this->i18nPrefix. $args[0];
    
    return rex_call_func(array($I18N, 'msg'), $args, false);
  }

  /**
   * Bindet die config-Datei eines Addons ein
   */
  protected abstract function includeConfig($addonName, $configFile);
  
  /**
   * Bindet die installations-Datei eines Addons ein
   */
  protected abstract function includeInstaller($addonName, $installFile);
  
  /**
   * Bindet die deinstallations-Datei eines Addons ein
   */
  protected abstract function includeUninstaller($addonName, $uninstallFile);
  
  /**
   * Speichert den aktuellen Zustand
   */
  protected abstract function generateConfig();
  
  /**
   * Ansprechen einer API funktion
   * 
   * @param $method Name der Funktion
   * @param $arguments Array von Parametern/Argumenten
   */
  protected abstract function apiCall($method, $arguments);
      
  /**
   * Laedt die package.yml in $REX
   */
  protected abstract function loadPackageInfos($addonName);
  
  /**
   * Findet den Basispfad eines Addons
   */
  protected abstract function baseFolder($addonName);
  
  /**
   * Findet den Basispfad für Media-Dateien
   */
  protected abstract function mediaFolder($addonName);
}
