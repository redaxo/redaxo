<?php

/**
 * Manager class for packages
 */
abstract class rex_package_manager extends rex_factory_base
{
  /**
   * @var rex_package
   */
  protected $package;

  protected $generatePackageOrder = true;

  private $i18nPrefix;

  private $message;

  /**
   * Constructor
   *
   * @param rex_package $package    Package
   * @param string      $i18nPrefix Prefix for i18n
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
    if (get_called_class() == __CLASS__) {
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
  public function install($installDump = true)
  {
    $state = true;

    // Pruefen des Addon Ornders auf Schreibrechte,
    // damit das Addon spaeter wieder geloescht werden kann
    $install_dir  = $this->package->getPath();
    if (!rex_dir::isWritable($install_dir)) {
      $state = $this->i18n('dir_not_writable', $install_dir);
    }

    if ($state === true) {
      if (!is_readable($this->package->getPath(rex_package::FILE_PACKAGE))) {
        $state = $this->i18n('missing_yml_file');
      } else {
        $packageId = $this->package->getProperty('package');
        if ($packageId === null) {
          $state = $this->i18n('missing_id', $this->package->getPackageId());
        } elseif ($packageId != $this->package->getPackageId()) {
          $parts = explode('/', $packageId, 2);
          $state = $this->wrongPackageId($parts[0], isset($parts[1]) ? $parts[1] : null);
        } elseif ($this->package->getVersion() === null) {
          $state = $this->i18n('missing_version');
        }
      }
    }

    // check if requirements are met
    if ($state === true) {
      $message = '';
      if (!$this->checkRequirements()) {
        $message = $this->message;
      }
      if (!$this->checkConflicts()) {
        $message .= $this->message;
      }
      $state = $message ?: true;
    }

    $this->package->setProperty('install', true);

    // check if install.php exists
    if ($state === true && is_readable($this->package->getPath(rex_package::FILE_INSTALL))) {
      rex_autoload::addDirectory($this->package->getPath('lib'));
      rex_autoload::addDirectory($this->package->getPath('vendor'));
      try {
        $this->package->includeFile(rex_package::FILE_INSTALL);
        // Wurde das "install" Flag gesetzt?
        // Fehlermeldung ausgegeben? Wenn ja, Abbruch
        if (($instmsg = $this->package->getProperty('installmsg', '')) != '') {
          $state = $instmsg;
        } elseif (!$this->package->isInstalled()) {
          $state = $this->i18n('no_reason');
        }
      } catch (rex_functional_exception $e) {
        $state = $e->getMessage();
      } catch (rex_sql_exception $e) {
        $state = 'SQL error: ' . $e->getMessage();
      }
    }

    $install_sql  = $this->package->getPath(rex_package::FILE_INSTALL_SQL);
    if ($state === true && $installDump === true && is_readable($install_sql)) {
      $state = rex_sql_util::importDump($install_sql);

      if ($state !== true)
        $state = 'Error found in install.sql:<br />' . $state;
    }

    // Installation ok
    if ($state === true) {
      $this->saveConfig();
    }

    // Dateien kopieren
    $files_dir = $this->package->getPath('assets');
    if ($state === true && is_dir($files_dir)) {
      if (!rex_dir::copy($files_dir, $this->package->getAssetsPath())) {
        $state = $this->i18n('install_cant_copy_files');
      }
    }

    if ($state !== true) {
      $this->package->setProperty('install', false);
      $state = $this->i18n('no_install', $this->package->getName()) . '<br />' . $state;
    }

    $this->message = $state === true ? $this->i18n('installed', $this->package->getName()) : $state;

    return $state === true;
  }

  /**
   * Uninstalls a package
   *
   * @param $installDump When TRUE, the sql dump will be importet
   *
   * @return boolean TRUE on success, FALSE on error
   */
  public function uninstall($installDump = true)
  {
    $state = true;

    $isActivated = $this->package->isActivated();
    if ($isActivated) {
      $state = $this->deactivate();
      if ($state !== true) {
        return $state;
      }
    }

    // start un-installation
    $this->package->setProperty('install', false);

    // check if uninstall.php exists
    if ($state === true && is_readable($this->package->getPath(rex_package::FILE_UNINSTALL))) {
      try {
        $this->package->includeFile(rex_package::FILE_UNINSTALL);
        // Wurde das "install" Flag gesetzt?
        // Fehlermeldung ausgegeben? Wenn ja, Abbruch
        if (($instmsg = $this->package->getProperty('installmsg', '')) != '') {
          $state = $instmsg;
        } elseif ($this->package->isInstalled()) {
          $state = $this->i18n('no_reason');
        }
      } catch (rex_functional_exception $e) {
        $state = $e->getMessage();
      } catch (rex_sql_exception $e) {
        $state = 'SQL error: ' . $e->getMessage();
      }
    }

    $uninstall_sql  = $this->package->getPath(rex_package::FILE_UNINSTALL_SQL);
    if ($state === true && $installDump === true && is_readable($uninstall_sql)) {
      $state = rex_sql_util::importDump($uninstall_sql);

      if ($state !== true)
        $state = 'Error found in uninstall.sql:<br />' . $state;
    }

    $mediaFolder = $this->package->getAssetsPath();
    if ($state === true && is_dir($mediaFolder)) {
      if (!rex_dir::delete($mediaFolder)) {
        $state = $this->i18n('install_cant_delete_files');
      }
    }

    if ($state === true) {
      rex_config::removeNamespace($this->package->getPackageId());
    }

    if ($state !== true) {
      // Fehler beim uninstall -> Addon bleibt installiert
      $this->package->setProperty('install', true);
      if ($isActivated) {
        $this->package->setProperty('status', true);
      }
      $this->saveConfig();
      $state = $this->i18n('no_uninstall', $this->package->getName()) . '<br />' . $state;
    } else {
      $this->saveConfig();
    }

    $this->message = $state === true ? $this->i18n('uninstalled', $this->package->getName()) : $state;

    return $state === true;
  }

  /**
   * Activates a package
   *
   * @return boolean TRUE on success, FALSE on error
   */
  public function activate()
  {
    if ($this->package->isInstalled()) {
      $state = '';
      if (!$this->checkRequirements()) {
        $state .= $this->message;
      }
      if (!$this->checkConflicts()) {
        $state .= $this->message;
      }
      $state = $state ?: true;

      if ($state === true) {
        $this->package->setProperty('status', true);
        if (!rex::isSetup()) {
          if (is_readable($this->package->getPath(rex_package::FILE_BOOT))) {
            rex_autoload::addDirectory($this->package->getPath('lib'));
            rex_autoload::addDirectory($this->package->getPath('vendor'));
            $this->package->includeFile(rex_package::FILE_BOOT);
          }
        }
        $this->saveConfig();
      }
      if ($state === true && $this->generatePackageOrder) {
        self::generatePackageOrder();
      }
    } else {
      $state = $this->i18n('not_installed', $this->package->getName());
    }

    if ($state !== true) {
      // error while config generation, rollback addon status
      $this->package->setProperty('status', false);
      $state = $this->i18n('no_activation', $this->package->getName()) . '<br />' . $state;
    }

    $this->message = $state === true ? $this->i18n('activated', $this->package->getName()) : $state;

    return $state === true;
  }

  /**
   * Deactivates a package
   *
   * @return boolean TRUE on success, FALSE on error
   */
  public function deactivate()
  {
    $state = $this->checkDependencies();

    if ($state === true) {
      $this->package->setProperty('status', false);
      $this->saveConfig();
    }

    if ($state === true) {
      // reload autoload cache when addon is deactivated,
      // so the index doesn't contain outdated class definitions
      rex_autoload::removeCache();

      if ($this->generatePackageOrder) {
        self::generatePackageOrder();
      }

      $this->message = $this->i18n('deactivated', $this->package->getName());
      return true;
    }

    $this->message = $this->i18n('no_deactivation', $this->package->getName()) . '<br />' . $this->message;
    return false;
  }

  /**
   * Deletes a package
   *
   * @return boolean TRUE on success, FALSE on error
   */
  public function delete()
  {
    $state = $this->_delete();
    self::synchronizeWithFileSystem();
    return $state;
  }

  /**
   * Deletes a package
   *
   * @param boolean $ignoreState
   *
   * @return boolean TRUE on success, FALSE on error
   */
  protected function _delete($ignoreState = false)
  {
    try {
      if (!$ignoreState && $this->package->isSystemPackage())
        return $this->i18n('systempackage_delete_not_allowed');

      // zuerst deinstallieren
      // bei erfolg, komplett löschen
      $state = true;
      $state = ($ignoreState || $state) && (!$this->package->isInstalled() || $this->uninstall());
      $state = ($ignoreState || $state) && rex_dir::delete($this->package->getPath());
      $state = ($ignoreState || $state) && rex_dir::delete($this->package->getDataPath());
      if (!$ignoreState) {
        $this->saveConfig();
      }
    }
    // addon-code which will be included might throw exception
    catch (Exception $e) {
      $state = $e->getMessage();
    }

    $this->message = $state === true ? $this->i18n('deleted', $this->package->getName()) : $state;

    return $ignoreState ? true : $state === true;
  }

  /**
   * @param string $addonName
   * @param string $pluginName
   * @return string
   */
  abstract protected function wrongPackageId($addonName, $pluginName = null);

  /**
   * Checks whether the requirements are met.
   *
   * @return boolean
   */
  public function checkRequirements()
  {
    if (!$this->checkRedaxoRequirement(rex::getVersion())) {
      return false;
    }

    $state = array();
    $requirements = $this->package->getProperty('requires', array());

    if (isset($requirements['php'])) {
      if (!is_array($requirements['php'])) {
        $requirements['php'] = array('version' => $requirements['php']);
      }
      if (isset($requirements['php']['version']) && !self::matchVersionConstraints(PHP_VERSION, $requirements['php']['version'])) {
        $state[] = $this->i18n('requirement_error_php_version', PHP_VERSION, $requirements['php']['version']);
      }
      if (isset($requirements['php']['extensions']) && $requirements['php']['extensions']) {
        $extensions = (array) $requirements['php']['extensions'];
        foreach ($extensions as $reqExt) {
          if (is_string($reqExt) && !extension_loaded($reqExt)) {
            $state[] = $this->i18n('requirement_error_php_extension', $reqExt);
          }
        }
      }
    }

    if (empty($state)) {
      if (isset($requirements['packages']) && is_array($requirements['packages'])) {
        foreach ($requirements['packages'] as $package => $_) {
          if (!$this->checkPackageRequirement($package)) {
            $state[] = $this->message;
          }
        }
      }
      $conflicts = $this->package->getProperty('conflicts', array());
      if (isset($conflicts['packages']) && is_array($conflicts['packages'])) {
        foreach ($conflicts['packages'] as $package => $_) {
          if (!$this->checkPackageConflict($package)) {
            $state[] = $this->message;
          }
        }
      }
    }

    if (empty($state)) {
      return true;
    }
    $this->message = implode('<br />', $state);
    return false;
  }

  /**
   * Checks whether the redaxo requirement is met.
   *
   * @param string $redaxoVersion REDAXO version
   * @return boolean
   */
  public function checkRedaxoRequirement($redaxoVersion)
  {
    $requirements = $this->package->getProperty('requires', array());
    if (isset($requirements['redaxo']) && !self::matchVersionConstraints($redaxoVersion, $requirements['redaxo'])) {
      $this->message = $this->i18n('requirement_error_redaxo_version', $redaxoVersion, $requirements['redaxo']);
      return false;
    }
    return true;
  }

  /**
   * Checks whether the package requirement is met.
   *
   * @param string $packageId Package ID
   * @return boolean
   */
  public function checkPackageRequirement($packageId)
  {
    $requirements = $this->package->getProperty('requires', array());
    if (!isset($requirements['packages'][$packageId])) {
      return true;
    }
    $package = rex_package::get($packageId);
    if (!$package->isAvailable()) {
      $this->message = $this->i18n('requirement_error_' . $package->getType(), $package->getPackageId());
      return false;
    } elseif (!self::matchVersionConstraints($package->getVersion(), $requirements['packages'][$packageId])) {
      $this->message = $this->i18n('requirement_error_' . $package->getType(), $package->getPackageId(), $package->getVersion(), $requirements['packages'][$packageId]);
      return false;
    }
    return true;
  }

  /**
   * Checks whether the package is in conflict with other packages
   *
   * @return boolean
   */
  public function checkConflicts()
  {
    $state = array();
    $conflicts = $this->package->getProperty('conflicts', array());

    if (isset($conflicts['packages']) && is_array($conflicts['packages'])) {
      foreach ($conflicts['packages'] as $package => $_) {
        if (!$this->checkPackageConflict($package)) {
          $state[] = $this->message;
        }
      }
    }

    if (empty($state)) {
      return true;
    }
    $this->message = implode('<br />', $state);
    return false;
  }

  /**
   * Checks whether the package is in conflict with another package
   *
   * @param string $packageId Package ID
   * @return boolean
   */
  public function checkPackageConflict($packageId)
  {
    $conflicts = $this->package->getProperty('conflicts', array());
    $package = rex_package::get($packageId);
    if (!isset($conflicts['packages'][$packageId]) || !$package->isAvailable()) {
      return true;
    }
    $constraints = $conflicts['packages'][$packageId];
    if (!is_string($constraints) || !$constraints || $constraints === '*') {
      $this->message = $this->i18n('conflict_error_' . $package->getType(), $package->getPackageId());
      return false;
    } elseif (self::matchVersionConstraints($package->getVersion(), $constraints)) {
      $this->message = $this->i18n('conflict_error_' . $package->getType() . '_version', $package->getPackageId(), $constraints);
      return false;
    }
    return true;
  }

  /**
   * Checks if another Package which is activated, depends on the given package
   *
   * @return boolean
   */
  public function checkDependencies()
  {
    $i18nPrefix = 'package_dependencies_error_';
    $state = array();

    foreach (rex_package::getAvailablePackages() as $package) {
      if ($package === $this->package || $package->getAddon() === $this->package)
        continue;

      $requirements = $package->getProperty('requires', array());
      if (isset($requirements['packages'][$this->package->getPackageId()])) {
        $state[] = rex_i18n::msg($i18nPrefix . $package->getType(), $package->getPackageId());
      }
    }

    if (empty($state)) {
      return true;
    }
    $this->message = implode('<br />', $state);
    return false;
  }

  /**
   * Translates the given key
   *
   * @param string $key Key
   *
   * @return string Tranlates text
   */
  protected function i18n($key)
  {
    $args = func_get_args();
    $key = $this->i18nPrefix . $args[0];
    if (!rex_i18n::hasMsg($key)) {
      $key = 'package_' . $args[0];
    }
    $args[0] = $key;

    return call_user_func_array(array('rex_i18n', 'msg'), $args);
  }

  /**
   * Generates the package order
   */
  static protected function generatePackageOrder()
  {
    $early = array();
    $normal = array();
    $late = array();
    $requires = array();
    $add = function ($id) use (&$add, &$normal, &$requires) {
      $normal[] = $id;
      unset($requires[$id]);
      foreach ($requires as $rp => &$ps) {
        unset($ps[$id]);
        if (empty($ps)) {
          $add($rp);
        }
      }
    };
    foreach (rex_package::getAvailablePackages() as $package) {
      $id = $package->getPackageId();
      $load = $package->getProperty('load');
      if ($package instanceof rex_plugin
        && !in_array($load, array('early', 'normal', 'late'))
        && in_array($addonLoad = $package->getAddon()->getProperty('load'), array('early', 'late'))
      ) {
        $load = $addonLoad;
      }
      if ($load === 'early') {
        $early[] = $id;
      } elseif ($load === 'late') {
        $late[] = $id;
      } else {
        $req = $package->getProperty('requires');
        if ($package instanceof rex_plugin) {
          $req['addons'][$package->getAddon()->getName()] = true;
        }
        if (isset($req['addons']) && is_array($req['addons'])) {
          foreach ($req['addons'] as $addonId => $reqP) {
            $addon = rex_addon::get($addonId);
            if (!in_array($addon, $normal) && !in_array($addon->getProperty('load'), array('early', 'late'))) {
              $requires[$id][$addonId] = true;
            }
            if (isset($reqP['plugins']) && is_array($reqP['plugins'])) {
              foreach ($reqP['plugins'] as $pluginName => $_) {
                $plugin = $addon->getPlugin($pluginName);
                $pluginId = $plugin->getPackageId();
                if (!in_array($pluginId, $normal) && !in_array($plugin->getProperty('load'), array('early', 'late'))) {
                  $requires[$id][$pluginId] = true;
                }
              }
            }
          }
        }
        if (!isset($requires[$id])) {
          $add($id);
        }
      }
    }
    rex::setConfig('package-order', array_merge($early, $normal, array_keys($requires), $late));
  }

  /**
   * Saves the package config
   */
  static protected function saveConfig()
  {
    $config = array();
    foreach (rex_addon::getRegisteredAddons() as $addonName => $addon) {
      $config[$addonName]['install'] = $addon->isInstalled();
      $config[$addonName]['status'] = $addon->isActivated();
      foreach ($addon->getRegisteredPlugins() as $pluginName => $plugin) {
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
    $addons = self::readPackageFolder(rex_path::src('addons'));
    $registeredAddons = array_keys(rex_addon::getRegisteredAddons());
    foreach (array_diff($registeredAddons, $addons) as $addonName) {
      $manager = rex_addon_manager::factory(rex_addon::get($addonName));
      $manager->_delete(true);
      unset($config[$addonName]);
    }
    foreach ($addons as $addonName) {
      if (!rex_addon::exists($addonName)) {
        $config[$addonName]['install'] = false;
        $config[$addonName]['status'] = false;
        $registeredPlugins = array();
      } else {
        $addon = rex_addon::get($addonName);
        $config[$addonName]['install'] = $addon->isInstalled();
        $config[$addonName]['status'] = $addon->isActivated();
        $registeredPlugins = array_keys($addon->getRegisteredPlugins());
      }
      $plugins = self::readPackageFolder(rex_path::addon($addonName, 'plugins'));
      foreach (array_diff($registeredPlugins, $plugins) as $pluginName) {
        $manager = rex_plugin_manager::factory(rex_plugin::get($addonName, $pluginName));
        $manager->_delete(true);
        unset($config[$addonName]['plugins'][$pluginName]);
      }
      foreach ($plugins as $pluginName) {
        $plugin = rex_plugin::get($addonName, $pluginName);
        $config[$addonName]['plugins'][$pluginName]['install'] = $plugin->isInstalled();
        $config[$addonName]['plugins'][$pluginName]['status'] = $plugin->isActivated();
      }
      if (isset($config[$addonName]['plugins']) && is_array($config[$addonName]['plugins']))
        ksort($config[$addonName]['plugins']);
    }
    ksort($config);

    rex::setConfig('package-config', $config);
    rex_addon::initialize();
  }

  /**
   * Checks the version of the requirement.
   *
   * @param string $version     Version
   * @param string $constraints Constraint list, separated by comma
   * @throws rex_exception
   * @return boolean
   */
  static private function matchVersionConstraints($version, $constraints)
  {
    $rawConstraints = array_filter(array_map('trim', explode(',', $constraints)));
    $constraints = array();
    foreach ($rawConstraints as $constraint) {
      if ($constraint === '*') {
        continue;
      }

      if (!preg_match('/^(?<op>==?|<=?|>=?|!=|~|) ?(?<version>\d+(?:\.\d+)*)(?<wildcard>\.\*)?(?<prerelease>[ -.]?[a-z]+(?:[ -.]?\d+)?)?$/i', $constraint, $match)
        || isset($match['wildcard']) && $match['wildcard'] && ($match['op'] != '' || isset($match['prerelease']) && $match['prerelease'])
      ) {
        throw new rex_exception('Unknown version constraint "' . $constraint . '"!');
      }

      if (isset($match['wildcard']) && $match['wildcard']) {
        $constraints[] = array('>=', $match['version']);
        $pos = strrpos($match['version'], '.') + 1;
        $sub = substr($match['version'], $pos);
        $constraints[] = array('<', substr_replace($match['version'], $sub + 1, $pos));
      } elseif ($match['op'] == '~') {
        $constraints[] = array('>=', $match['version'] . (isset($match['prerelease']) ? $match['prerelease'] : ''));
        if (($pos = strrpos($match['version'], '.')) === false) {
          $constraints[] = array('<', $match['version'] + 1);
        } else {
          $main = '';
          $sub = substr($match['version'], 0, $pos);
          if (($pos = strrpos($sub, '.')) !== false) {
            $main = substr($sub, 0, $pos + 1);
            $sub = substr($sub, $pos + 1);
          }
          $constraints[] = array('<', $main . ($sub + 1));
        }
      } else {
        $constraints[] = array($match['op'] ?: '=', $match['version']);
      }
    }

    foreach ($constraints as $constraint) {
      if (!rex_string::compareVersions($version, $constraint[1], $constraint[0])) {
        return false;
      }
    }

    return true;
  }

  /**
   * Returns the subfolders of the given folder
   *
   * @param string $folder Folder
   */
  static private function readPackageFolder($folder)
  {
    $packages = array();

    if (is_dir($folder)) {
      foreach (rex_finder::factory($folder)->dirsOnly() as $file) {
        $packages[] = $file->getBasename();
      }
    }

    return $packages;
  }
}
