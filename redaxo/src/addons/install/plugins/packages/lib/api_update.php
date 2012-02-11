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
    $temppath = rex_path::addon('_new_'. $this->addonkey);
    if(($msg = $this->extractArchiveTo($temppath)) !== true)
    {
      return $msg;
    }
    if($this->addon->isActivated())
    {
      if(file_exists($temppath .'package.yml'))
      {
        $config = sfYaml::load($temppath .'package.yml');
        if(isset($config['requires']))
        {
          $req = $this->addon->getProperty('requires');
          $this->addon->setProperty('requires', $config['requires']);
          $manager = rex_addon_manager::factory($this->addon);
          if(($msg = $manager->checkRequirements()) !== true)
          {
            $this->addon->setProperty('requires', $req);
            return $msg;
          }
        }
      }
      $version = isset($config['version']) ? $config['version'] : $this->file['version'];
      $oldVersion = $this->addon->getVersion();
      $this->addon->setProperty('version', $version);
      $messages = array();
      foreach(rex_addon::getAvailableAddons() as $addon)
      {
        if($addon != $this->addon)
        {
          $manager = rex_addon_manager::factory($addon);
          if(($msg = $manager->checkPackageRequirement($this->addon->getPackageId())) !== true)
          {
            $messages[] = $addon->getPackageId() .': '. $msg;
          }
        }
        foreach($addon->getAvailablePlugins() as $plugin)
        {
          $manager = rex_plugin_manager::factory($plugin);
          if(($msg = $manager->checkPackageRequirement($this->addon->getPackageId())) !== true)
          {
            $messages[] = $plugin->getPackageId() .': '. $msg;
          }
        }
      }
      $this->addon->setProperty('version', $oldVersion);
      if(!empty($messages))
      {
        return implode('<br />', $messages);
      }
    }
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
    $path = rex_path::addon($this->addonkey);
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
    rex_dir::delete($path);
    rename($temppath, $path);
    $origAssets = $this->addon->getBasePath('assets');
    if($this->addon->isInstalled() && is_dir($origAssets))
    {
      rex_dir::copy($origAssets, $assets);
    }
    $this->addon->setProperty('version', $this->file['version']);
    rex_install_packages::updatedPackage($this->addonkey, $this->fileId);
  }

  public function __destruct()
  {
    rex_dir::delete(rex_path::addon('_new_'. $this->addonkey));
  }
}
