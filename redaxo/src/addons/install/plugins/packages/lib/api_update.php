<?php

class rex_api_install_packages_update extends rex_api_install_packages_download
{
  const
    GET_PACKAGES_FUNCTION = 'getUpdatePackages',
    VERB = 'updated',
    SHOW_LINK = false;

  private $addon;

  protected function checkPreConditions()
  {
    if(!rex_addon::exists($this->addonkey))
    {
      throw new rex_api_exception(sprintf('AddOn "%s" does not exist!', $this->addonkey));
    }
    $this->addon = rex_addon::get($this->addonkey);
    if(!rex_string::compareVersions($this->file['version'], $this->addon->getVersion(), '>'))
    {
      throw new rex_api_exception(sprintf('Existing version of AddOn "%s" (%s) is newer than %s', $this->addonkey, $this->addon->getVersion(), $this->file['version']));
    }
  }

  public function doAction()
  {
    $path = rex_path::addon($this->addonkey);
    $temppath = rex_path::addon('_new_'. $this->addonkey);

    if(($msg = $this->extractArchiveTo($temppath)) !== true)
    {
      return $msg;
    }

    if($this->addon->isAvailable() && ($msg = $this->checkRequirements()) !== true)
    {
      return $msg;
    }

    // ---- include update.inc.php
    if($this->addon->isInstalled() && file_exists($temppath .'update.inc.php'))
    {
      rex_addon_manager::includeFile($this->addon, '../_new_'. $this->addonkey .'/update.inc.php');
      if(($msg = $this->addon->getProperty('updatemsg', '')) != '')
      {
        return $msg;
      }
      if(!$this->addon->getProperty('update'))
      {
        return rex_i18n::msg('addon_no_reason');
      }
    }

    // ---- backup
    $assets = $this->addon->getAssetsPath('', rex_path::ABSOLUTE);
    if(rex_addon::get('install')->getConfig('backups'))
    {
      $archivePath = rex_path::pluginData('install', 'packages', $this->addonkey .'/');
      rex_dir::create($archivePath);
      $archive = $archivePath . strtolower(preg_replace("/[^a-z0-9-_.]/i", "_", $this->addon->getVersion('0'))) .'.zip';
      rex_install_helper::copyDirToArchive($path, $archive);
      if(is_dir($assets))
      {
        rex_install_helper::copyDirToArchive($assets, $archive, 'assets');
      }
    }

    // ---- copy plugins to new addon dir
    foreach($this->addon->getRegisteredPlugins() as $plugin)
    {
      $pluginPath = $temppath .'/plugins/'. $plugin->getName();
      if(!is_dir($pluginPath))
      {
        rex_dir::copy($plugin->getBasePath(), $pluginPath);
      }
      elseif($plugin->isInstalled() && is_dir($pluginPath .'/assets'))
      {
        rex_dir::copy($pluginPath .'/assets', $plugin->getAssetsPath('', rex_path::ABSOLUTE));
      }
    }

    // ---- update main addon dir
    rex_dir::delete($path);
    rename($temppath, $path);

    // ---- update assets
    $origAssets = $this->addon->getBasePath('assets');
    if($this->addon->isInstalled() && is_dir($origAssets))
    {
      rex_dir::copy($origAssets, $assets);
    }

    $this->addon->setProperty('version', $this->file['version']);
    rex_install_packages::updatedPackage($this->addonkey, $this->fileId);
  }

  private function checkRequirements()
  {
    $temppath = rex_path::addon('_new_'. $this->addonkey);

    // ---- update "version" and "requires" properties
    $versions = new SplObjectStorage;
    $requirements = new SplObjectStorage;
    if(file_exists($temppath .'package.yml'))
    {
      $config = rex_file::getConfig($temppath .'package.yml');
      if(isset($config['requires']))
      {
        $requirements[$this->addon] = $this->addon->getProperty('requires');
        $this->addon->setProperty('requires', $config['requires']);
      }
    }
    $versions[$this->addon] = $this->addon->getVersion();
    $this->addon->setProperty('version', isset($config['version']) ? $config['version'] : $this->file['version']);
    $availablePlugins = $this->addon->getAvailablePlugins();
    foreach($availablePlugins as $plugin)
    {
      if(is_dir($temppath .'/plugins/'. $plugin->getName()))
      {
        $config = rex_file::getConfig($temppath .'/plugins/'. $plugin->getName() .'/package.yml');
        if(isset($config['requires']))
        {
          $requirements[$plugin] = $plugin->getProperty('requires');
          $plugin->setProperty('requires', $config['requires']);
        }
        if(isset($config['version']))
        {
          $versions[$plugin] = $plugin->getProperty('version');
          $plugin->setProperty('requires', $config['version']);
        }
      }
    }

    // ---- check requirements
    $message = rex_addon_manager::factory($this->addon)->checkRequirements();

    if($message === true)
    {
      $messages = array();

      foreach($availablePlugins as $plugin)
      {
        $msg = rex_plugin_manager::factory($plugin)->checkRequirements();
        if($msg !== true)
        {
          $messages[] = $plugin->getPackageId() .': '. $msg;
        }
      }
      foreach(rex_addon::getAvailableAddons() as $addon)
      {
        if($addon == $this->addon)
          continue;
        $manager = rex_addon_manager::factory($addon);
        if(($msg = $manager->checkPackageRequirement($this->addon->getPackageId())) !== true)
        {
          $messages[] = $addon->getPackageId() .': '. $msg;
        }
        else
        {
          foreach($versions as $reqPlugin)
          {
            if(($msg = $manager->checkPackageRequirement($reqPlugin->getPackageId())) !== true)
            {
              $messages[] = $addon->getPackageId() .': '. $msg;
            }
          }
        }
        foreach($addon->getAvailablePlugins() as $plugin)
        {
          $manager = rex_plugin_manager::factory($plugin);
          if(($msg = $manager->checkPackageRequirement($this->addon->getPackageId())) !== true)
          {
            $messages[] = $plugin->getPackageId() .': '. $msg;
          }
          else
          {
            foreach($versions as $reqPlugin)
            {
              if(($msg = $manager->checkPackageRequirement($reqPlugin->getPackageId())) !== true)
              {
                $messages[] = $plugin->getPackageId() .': '. $msg;
              }
            }
          }
        }
      }

      $message = empty($messages) ? true : implode('<br />', $messages);
    }

    // ---- reset "version" and "requires" properties
    foreach($versions as $package)
    {
      $package->setProperty('version', $versions[$package]);
    }
    foreach($requirements as $package)
    {
      $package->setProperty('requires', $versions[$package]);
    }

    return $message;
  }

  public function __destruct()
  {
    rex_dir::delete(rex_path::addon('_new_'. $this->addonkey));
  }
}
