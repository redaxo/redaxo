<?php

/**
 * Manager class for packages
 */
abstract class rex_package_manager extends rex_factory
{
  const
    PACKAGE_FILE = 'package.yml',
    CONFIG_FILE = 'config.inc.php',
    INSTALL_FILE = 'install.inc.php',
    INSTALL_SQL = 'install.sql',
    UNINSTALL_FILE = 'uninstall.inc.php',
    UNINSTALL_SQL = 'uninstall.sql',
    ASSETS_FOLDER = 'assets';

  /**
   * @var rex_package
   */
  protected $package;

  private $i18nPrefix;

  private $message;

  /**
   * Constructor
   *
   * @param rex_package $package Package
   * @param string $i18nPrefix Prefix for i18n
   */
  protected function __construct(rex_package $package, $i18nPrefix)
  {
    $this->package = $package;
    $this->i18nPrefix = $i18nPrefix;
  }

  /**
   * Creates the manager for the package
   *
   * @param rex_package $package Package
   *
   * @return rex_package_manager
   */
  static public function factory(rex_package $package)
  {
    if(get_called_class() == __CLASS__)
    {
      $class = $package instanceof rex_plugin ? 'rex_plugin_manager' : 'rex_addon_manager';
      return $class::factory($package);
    }
    $class = static::getFactoryClass();
    return new $class($package);
  }

  /**
   * Returns the message
   *
   * @return string
   */
  public function getMessage()
  {
    return $this->message;
  }

  /**
   * Installs a package
   *
   * @param $installDump When TRUE, the sql dump will be importet
   *
   * @return boolean TRUE on success, FALSE on error
   */
  public function install($installDump = TRUE)
  {
    $state = TRUE;

    $install_dir  = $this->package->getBasePath();
    $install_file = $install_dir . self::INSTALL_FILE;
    $install_sql  = $install_dir . self::INSTALL_SQL;
    $config_file  = $install_dir . self::CONFIG_FILE;
    $files_dir    = $install_dir . self::ASSETS_FOLDER;

    try {
      // Pruefen des Addon Ornders auf Schreibrechte,
      // damit das Addon spaeter wieder geloescht werden kann
      $state = rex_is_writable($install_dir);

      if ($state === TRUE)
      {
        // load package infos
        self::loadPackageInfos($this->package);

        // check if requirements are met
        $state = $this->checkRequirements();

        if($state === TRUE)
        {
          // check if install.inc.php exists
          if (is_readable($install_file))
          {
            static::includeFile($this->package, self::INSTALL_FILE);
            $state = $this->verifyInstallation();
          }
          else
          {
            // no install file -> no error
            $this->package->setProperty('install', true);
          }

          if($state === TRUE && $installDump === TRUE && is_readable($install_sql))
          {
            $state = rex_sql_dump::import($install_sql);

            if($state !== TRUE)
              $state = 'Error found in install.sql:<br />'. $state;
          }

          // Installation ok
          if ($state === TRUE)
          {
            $this->saveConfig();
          }
        }
      }

      // Dateien kopieren
      if($state === TRUE && is_dir($files_dir))
      {
        if(!rex_dir::copy($files_dir, $this->package->getAssetsPath('', rex_path::ABSOLUTE)))
        {
          $state = $this->I18N('install_cant_copy_files');
        }
      }
    // addon-code which will be included might throw exception
    }
    catch (Exception $e)
    {
      $state = $e->getMessage();
    }

    if($state !== TRUE)
    {
      $this->package->setProperty('install', false);
      $state = $this->I18N('no_install', $this->package->getName()) .'<br />'. $state;
    }

    $this->message = $state === TRUE ? $this->I18N('installed', $this->package->getName()) : $state;

    return $state === TRUE;
  }

  /**
   * Uninstalls a package
   *
   * @param $installDump When TRUE, the sql dump will be importet
   *
   * @return boolean TRUE on success, FALSE on error
   */
  public function uninstall($installDump = TRUE)
  {
    $state = TRUE;

    $install_dir    = $this->package->getBasePath();
    $uninstall_file = $install_dir . self::UNINSTALL_FILE;
    $uninstall_sql  = $install_dir . self::UNINSTALL_SQL;

    $isActivated = $this->package->isActivated();
    if ($isActivated)
    {
      $state = $this->deactivate();
      if ($state !== true)
      {
        return $state;
      }
    }

    try {
      // start un-installation
      if($state === TRUE)
      {
        // check if uninstall.inc.php exists
        if (is_readable($uninstall_file))
        {
          static::includeFile($this->package, self::UNINSTALL_FILE);
          $state = $this->verifyUninstallation();
        }
        else
        {
          // no uninstall file -> no error
          $this->package->setProperty('install', false);
        }
      }

      if($state === TRUE && $installDump === TRUE && is_readable($uninstall_sql))
      {
        $state = rex_sql_dump::import($uninstall_sql);

        if($state !== TRUE)
          $state = 'Error found in uninstall.sql:<br />'. $state;
      }

      $mediaFolder = $this->package->getAssetsPath('', rex_path::ABSOLUTE);
      if($state === TRUE && is_dir($mediaFolder))
      {
        if(!rex_dir::delete($mediaFolder))
        {
          $state = $this->I18N('install_cant_delete_files');
        }
      }

      if($state === TRUE)
      {
        rex_config::removeNamespace($this->package->getPackageId());
      }

    }
    // addon-code which will be included might throw exception
    catch (Exception $e)
    {
      $state = $e->getMessage();
    }

    if($state !== TRUE)
    {
      // Fehler beim uninstall -> Addon bleibt installiert
      $this->package->setProperty('install', true);
      if($isActivated)
      {
        $this->package->setProperty('status', true);
      }
      $this->saveConfig();
      $state = $this->I18N('no_uninstall', $this->package->getName()) .'<br />'. $state;
    }
    else
    {
      $this->saveConfig();
    }

    $this->message = $state === TRUE ? $this->I18N('uninstalled', $this->package->getName()) : $state;

    return $state === TRUE;
  }

  /**
   * Activates a package
   *
   * @return boolean TRUE on success, FALSE on error
   */
  public function activate()
  {
    try
    {
      if ($this->package->isInstalled())
      {
        // load package infos
        self::loadPackageInfos($this->package);

        $state = $this->checkRequirements();

        if ($state === TRUE)
        {
          $this->package->setProperty('status', true);
          if(!rex::isSetup())
          {
            if(is_readable($this->package->getBasePath(self::CONFIG_FILE)))
            {
              rex_autoload::addDirectory($this->package->getBasePath('lib'));
              static::includeFile($this->package, self::CONFIG_FILE);
            }
          }
          $this->saveConfig();
        }
        if($state === TRUE)
        {
          $this->addToPackageOrder();
        }
      }
      else
      {
        $state = $this->I18N('not_installed', $this->package->getName());
      }
    }
    // addon-code which will be included might throw exception
    catch (Exception $e)
    {
      $state = $e->getMessage();
    }

    if($state !== TRUE)
    {
      // error while config generation, rollback addon status
      $this->package->setProperty('status', false);
      $state = $this->I18N('no_activation', $this->package->getName()) .'<br />'. $state;
    }

    $this->message = $state === TRUE ? $this->I18N('activated', $this->package->getName()) : $state;

    return $state === TRUE;
  }

  /**
   * Deactivates a package
   *
   * @return boolean TRUE on success, FALSE on error
   */
  public function deactivate()
  {
    try
    {
      $state = $this->checkDependencies();

      if ($state === TRUE)
      {
        $this->package->setProperty('status', false);
        $this->saveConfig();
      }

      if($state === TRUE)
      {
        // reload autoload cache when addon is deactivated,
        // so the index doesn't contain outdated class definitions
        rex_autoload::removeCache();

        $this->removeFromPackageOrder();
      }
      else
      {
        $state = $this->I18N('no_deactivation', $this->package->getName()) .'<br />'. $state;
      }
    }
    // addon-code which will be included might throw exception
    catch (Exception $e)
    {
      $state = $e->getMessage();
    }

    $this->message = $state === TRUE ? $this->I18N('deactivated', $this->package->getName()) : $state;

    return $state === TRUE;
  }

  /**
   * Deletes a package
   *
   * @return boolean TRUE on success, FALSE on error
   */
  public function delete()
  {
    return $this->_delete();
  }

  /**
   * Deletes a package
   *
   * @param boolean $ignoreState
   *
   * @return boolean TRUE on success, FALSE on error
   */
  protected function _delete($ignoreState = FALSE)
  {
    try
    {
      if(!$ignoreState && $this->package->isSystemPackage())
        return $this->I18N('systempackage_delete_not_allowed');

      // zuerst deinstallieren
      // bei erfolg, komplett löschen
      $state = TRUE;
      $state = ($ignoreState || $state) && (!$this->package->isInstalled() || $this->uninstall());
      $state = ($ignoreState || $state) && rex_dir::delete($this->package->getBasePath());
      $state = ($ignoreState || $state) && rex_dir::delete($this->package->getDataPath());
      if(!$ignoreState)
      {
        $this->saveConfig();
      }
    }
    // addon-code which will be included might throw exception
    catch (Exception $e)
    {
      $state = $e->getMessage();
    }

    $this->message = $state === TRUE ? $this->I18N('deleted', $this->package->getName()) : $state;

    return $ignoreState ? TRUE : $state === TRUE;
  }

  /**
   * Verifies if the installation of the given Addon was successfull.
   */
  private function verifyInstallation()
  {
    $state = TRUE;

    // Wurde das "install" Flag gesetzt?
    // Fehlermeldung ausgegeben? Wenn ja, Abbruch
    if(($instmsg = $this->package->getProperty('installmsg', '')) != '')
    {
      $state = $instmsg;
    }
    elseif(!$this->package->isInstalled())
    {
      $state = $this->I18N('no_reason');
    }

    return $state;
  }

  /**
   * Verifies if the un-installation of the given Addon was successfull.
   */
  private function verifyUninstallation()
  {
    $state = TRUE;

    // Wurde das "install" Flag gesetzt?
    // Fehlermeldung ausgegeben? Wenn ja, Abbruch
    if(($instmsg = $this->package->getProperty('installmsg', '')) != '')
    {
      $state = $instmsg;
    }
    elseif($this->package->isInstalled())
    {
      $state = $this->I18N('no_reason');
    }

    return $state;
  }

  /**
   * Checks whether the requirements are met.
   */
  protected function checkRequirements()
  {
    $state = array();
    $requirements = $this->package->getProperty('requires', array());

    if(isset($requirements['redaxo']) && is_array($requirements['redaxo']))
    {
      $rexVers = rex::getVersion();
      if (($msg = $this->checkRequirementVersion('redaxo_', $requirements['redaxo'], $rexVers)) !== true)
      {
        return $msg;
      }
    }

    if(isset($requirements['php']) && is_array($requirements['php']))
    {
      if(($msg = $this->checkRequirementVersion('php_', $requirements['php'], PHP_VERSION)) !== true)
      {
        $state[] = $msg;
      }
      if(isset($requirements['php']['extensions']) && $requirements['php']['extensions'])
      {
        $extensions = (array) $requirements['php']['extensions'];
        foreach($extensions as $reqExt)
        {
          if(is_string($reqExt) && !extension_loaded($reqExt))
          {
            $state[] = rex_i18n::msg('addon_requirement_error_php_extension', $reqExt);
          }
        }
      }
    }

    if(empty($state) && isset($requirements['addons']) && is_array($requirements['addons']))
    {
      foreach($requirements['addons'] as $depName => $depAttr)
      {
        // check if dependency exists
        if(!rex_addon::exists($depName) || !rex_addon::get($depName)->isAvailable())
        {
          $state[] = rex_i18n::msg('addon_requirement_error_addon', $depName);
        }
        else
        {
          $addon = rex_addon::get($depName);

          if(($msg = $this->checkRequirementVersion('addon_', $depAttr, $addon->getVersion(), $depName)) !== true)
          {
            $state[] = $msg;
          }

          // check plugin requirements
          if(isset($depAttr['plugins']) && is_array($depAttr['plugins']))
          {
            foreach($depAttr['plugins'] as $pluginName => $pluginAttr)
            {
              // check if dependency exists
              if(!rex_plugin::exists($depName, $pluginName) || !rex_plugin::get($depName, $pluginName)->isAvailable())
              {
                $state[] = rex_i18n::msg('addon_requirement_error_plugin', $depName, $pluginName);
              }
              elseif(($msg = $this->checkRequirementVersion('plugin_', $pluginAttr, rex_plugin::get($depName, $pluginName)->getVersion(), $depName, $pluginName)) !== true)
              {
                $state[] = $msg;
              }
            }
          }
        }
      }
    }

    return empty($state) ? TRUE : implode('<br />', $state);
  }

  /**
   * Checks the version of the requirement.
   *
   * @param string $i18nPrefix Prefix for I18N
   * @param array $attributes Requirement attributes (version, min-version, max-version)
   * @param string $version Active version of requirement
   * @param string $addonName Name of the required addon, only necessary if requirement is a addon/plugin
   * @param string $pluginName Name of the required plugin, only necessary if requirement is a plugin
   */
  private function checkRequirementVersion($i18nPrefix, array $attributes, $version, $addonName = null, $pluginName = null)
  {
    $i18nPrefix = 'addon_requirement_error_'. $i18nPrefix;
    $state = TRUE;

    // check dependency exact-version
    if(isset($attributes['version']) && rex_version_compare($version, $attributes['version'], '!='))
    {
      $state = rex_i18n::msg($i18nPrefix . 'exact_version', $attributes['version'], $version, $addonName, $pluginName);
    }
    else
    {
      // check dependency min-version
      if(isset($attributes['min-version']) && rex_version_compare($version, $attributes['min-version'], '<'))
      {
        $state = rex_i18n::msg($i18nPrefix . 'min_version', $attributes['min-version'], $version, $addonName, $pluginName);
      }
      // check dependency max-version
      else if(isset($attributes['max-version']) && rex_version_compare($version, $attributes['max-version'], '>'))
      {
        $state = rex_i18n::msg($i18nPrefix . 'max_version', $attributes['max-version'], $version, $addonName, $pluginName);
      }
    }
    return $state;
  }

  /**
   * Checks if another Addon which is activated, depends on the given addon
   */
  protected abstract function checkDependencies();

	/**
   * Adds the package to the package order
   */
  protected function addToPackageOrder()
  {
    $order = rex::getConfig('package-order', array());
    $package = $this->package->getPackageId();
    if(!in_array($package, $order))
    {
      $name = $this->package->getAddon()->getName();
      if(in_array($name, array('users', 'compat')))
      {
        for($i = 0; rex_package::get($order[$i])->getAddon()->getName() == $name; ++$i);
        array_splice($order, $i, 0, array($package));
      }
      else
      {
        $order[] = $package;
      }
      rex::setConfig('package-order', $order);
    }
  }

  /**
   * Removes the package from the package order
   */
  protected function removeFromPackageOrder()
  {
    $order = rex::getConfig('package-order', array());
    if(($key = array_search($this->package->getPackageId(), $order)) !== FALSE)
    {
      unset($order[$key]);
      rex::setConfig('package-order', array_values($order));
    }
  }

  /**
   * Translates the given key
   *
   * @param string $key Key
   *
   * @return string Tranlates text
   */
  protected function I18N()
  {
    $args = func_get_args();
    $args[0] = $this->i18nPrefix. $args[0];

    return call_user_func_array(array('rex_i18n', 'msg'), $args);
  }

  /**
   * Includes a file inside the package context
   *
   * @param rex_package $package Package
   * @param string $file
   */
  static public function includeFile(rex_package $package, $file)
  {
    if(get_called_class() == __CLASS__)
    {
      $class = $package instanceof rex_plugin ? 'rex_plugin_manager' : 'rex_addon_manager';
      return $class::includeFile($package, $file);
    }
    if(static::hasFactoryClass())
    {
      return static::callFactoryClass(__FUNCTION__, func_get_args());
    }
    return $package->includeFile($file);
  }

  /**
   * Loads the package infos
   *
   * @param rex_package $package Package
   */
  static public function loadPackageInfos(rex_package $package)
  {
    $package_file = $package->getBasePath(self::PACKAGE_FILE);

    if(is_readable($package_file))
    {
      $ymlConfig = rex_file::getConfig($package_file);
      if($ymlConfig)
      {
        foreach($ymlConfig as $confName => $confValue)
        {
          $package->setProperty($confName, rex_i18n::translateArray($confValue));
        }
      }
    }
  }

  /**
   * Saves the package config
   */
  static protected function saveConfig()
  {
    $config = array();
    foreach(rex_addon::getRegisteredAddons() as $addonName => $addon)
    {
      $config[$addonName]['install'] = $addon->isInstalled();
      $config[$addonName]['status'] = $addon->isActivated();
      foreach($addon->getRegisteredPlugins() as $pluginName => $plugin)
      {
        $config[$addonName]['plugins'][$pluginName]['install'] = $plugin->isInstalled();
        $config[$addonName]['plugins'][$pluginName]['status'] = $plugin->isActivated();
      }
    }
    rex::setConfig('package-config', $config);
  }

  /**
   * Synchronizes the packages with the file system
   */
  static public function synchronizeWithFileSystem()
  {
    $config = rex::getConfig('package-config');
    $addons = self::readPackageFolder(rex_path::addon('*'));
    $registeredAddons = array_keys(rex_addon::getRegisteredAddons());
    foreach(array_diff($registeredAddons, $addons) as $addonName)
    {
      $manager = rex_addon_manager::factory(rex_addon::get($addonName));
      $manager->_delete(true);
      unset($config[$addonName]);
    }
    foreach($addons as $addonName)
    {
      if(!rex_addon::exists($addonName))
      {
        $config[$addonName]['install'] = FALSE;
        $config[$addonName]['status'] = FALSE;
        $registeredPlugins = array();
      }
      else
      {
        $addon = rex_addon::get($addonName);
        $config[$addonName]['install'] = $addon->isInstalled();
        $config[$addonName]['status'] = $addon->isActivated();
        $registeredPlugins = array_keys($addon->getRegisteredPlugins());
      }
      $plugins = self::readPackageFolder(rex_path::plugin($addonName, '*'));
      foreach(array_diff($registeredPlugins, $plugins) as $pluginName)
      {
        $manager = rex_plugin_manager::factory(rex_plugin::get($addonName, $pluginName));
        $manager->_delete(true);
        unset($config[$addonName]['plugins'][$pluginName]);
      }
      foreach($plugins as $pluginName)
      {
        $plugin = rex_plugin::get($addonName, $pluginName);
        $config[$addonName]['plugins'][$pluginName]['install'] = $plugin->isInstalled();
        $config[$addonName]['plugins'][$pluginName]['status'] = $plugin->isActivated();
      }
      if(isset($config[$addonName]['plugins']) && is_array($config[$addonName]['plugins']))
        ksort($config[$addonName]['plugins']);
    }
    ksort($config);

    rex::setConfig('package-config', $config);
    rex_addon::initialize();
  }

  /**
   * Returns the subfolders of the given folder
   *
   * @param string $folder Folder
   */
  static private function readPackageFolder($folder)
  {
    $packages = array ();

    $files = glob(rtrim($folder, DIRECTORY_SEPARATOR), GLOB_NOSORT);
    if(is_array($files))
    {
      foreach($files as $file)
      {
        $packages[] = basename($file);
      }
    }

    return $packages;
  }
}
