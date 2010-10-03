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
    
    // Prüfen des Addon Ornders auf Schreibrechte,
    // damit das Addon später wieder gelöscht werden kann
    $state = rex_is_writable($install_dir);
    
    if ($state === TRUE)
    {
      if (is_readable($install_file))
      {
        $this->includeInstaller($addonName, $install_file);
  
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
        else
        {
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
  
    if (is_readable($uninstall_file))
    {
      $this->includeUninstaller($addonName, $uninstall_file);
  
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
      else
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
  
    if($state !== TRUE)
      $this->apiCall('setProperty', array($addonName, 'status', 1));
      
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
   * Findet den Basispfad eines Addons
   */
  protected abstract function baseFolder($addonName);
  
  /**
   * Findet den Basispfad für Media-Dateien
   */
  protected abstract function mediaFolder($addonName);
}
