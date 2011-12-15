<?php

abstract class rex_api_install_packages_download extends rex_api_function
{
  protected
    $addonkey,
    $fileId,
    $file,
    $archive;

  public function execute()
  {
    if(!rex::getUser()->isAdmin())
    {
      throw new rex_api_exception('You do not have the permission!');
    }
    $this->addonkey = rex_request('addonkey', 'string');
    $function = static::GET_PACKAGES_FUNCTION;
    $packages = rex_install_packages::$function();
    $this->fileId = rex_request('file', 'int');
    if(!isset($packages[$this->addonkey]['files'][$this->fileId]))
    {
      return null;
    }
    $this->file = $packages[$this->addonkey]['files'][$this->fileId];
    $this->checkPreConditions();
    try
    {
      $archivefile = rex_install_webservice::getArchive($this->file['path']);
    }
    catch(rex_functional_exception $e)
    {
      throw new rex_api_exception($e->getMessage());
    }
    $message = '';
    $this->archive = "phar://$archivefile/". $this->addonkey;
    if($this->file['checksum'] != md5_file($archivefile))
    {
      $message = rex_i18n::msg('install_packages_warning_zip_wrong_checksum');
    }
    elseif(!file_exists($this->archive))
    {
      $message = rex_i18n::msg('install_packages_warning_zip_wrong_format');
    }
    elseif(is_string($msg = $this->doAction()))
    {
      $message = $msg;
    }
    rex_file::delete($archivefile);
    if($message)
    {
      $message = rex_i18n::msg('install_packages_warning_not_'. static::VERB, $this->addonkey) .'<br />'. $message;
      $success = false;
    }
    else
    {
      $message = rex_i18n::msg('install_packages_info_addon_'. static::VERB, $this->addonkey)
               . (static::SHOW_LINK ? ' <a href="index.php?page=addon">'. rex_i18n::msg('install_packages_to_addon_page') .'</a>' : '');
      $success = true;
      unset($_REQUEST['addonkey']);
    }
    return new rex_api_result($success, $message);
  }

  protected function extractArchiveTo($dir)
  {
    if(!rex_dir::copy($this->archive, $dir))
    {
      rex_dir::delete($dir);
      return rex_i18n::msg('install_packages_warning_zip_not_extracted');
    }
    return true;
  }

  abstract protected function checkPreConditions();

  abstract protected function doAction();
}

class rex_api_install_packages_add extends rex_api_install_packages_download
{
  const
    GET_PACKAGES_FUNCTION = 'getAddPackages',
    VERB = 'downloaded',
    SHOW_LINK = true;

  protected function checkPreConditions()
  {
    if(rex_addon::exists($this->addonkey))
    {
      throw new rex_api_exception(sprintf('AddOn "%s" already exist!', $this->addonkey));
    }
  }

  protected function doAction()
  {
    if(($msg = $this->extractArchiveTo(rex_path::addon($this->addonkey))) !== true)
    {
      return $msg;
    }
    rex_package_manager::synchronizeWithFileSystem();
    rex_install_packages::addedPackage($this->addonkey);
  }
}

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
    if(!rex_version_compare($this->file['version'], $this->addon->getVersion(), '>'))
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
      if(!empty($messages))
      {
        return implode('<br />', $messages);
      }
      $this->addon->setProperty('version', $oldVersion);
      if($msg !== true)
      {
        return $msg;
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
        return $this->I18N('no_reason');
      }
    }
    $path = rex_path::addon($this->addonkey);
    $archivePath = rex_path::pluginData('install', 'packages', $this->addonkey .'/');
    rex_dir::create($archivePath);
    $archive = $archivePath . strtolower(preg_replace("/[^a-z0-9-_.]/i", "_", $this->addon->getVersion('0'))) .'.zip';
    rex_install_helper::copyDirToArchive($path, $archive);
    $assets = $this->addon->getAssetsPath('', rex_path::ABSOLUTE);
    if(is_dir($assets))
    {
      rex_install_helper::copyDirToArchive($assets, $archive, 'assets');
    }
    rex_dir::delete($path);
    rename($temppath, $path);
    $origAssets = $this->addon->getBasePath('assets');
    if($this->addon->isInstalled() && is_dir($origAssets))
    {
      rex_dir::copy($origAssets, $assets);
    }
    rex_install_packages::updatedPackage($this->addonkey, $this->fileId);
  }

  public function __destruct()
  {
    rex_dir::delete(rex_path::addon('_new_'. $this->addonkey));
  }
}