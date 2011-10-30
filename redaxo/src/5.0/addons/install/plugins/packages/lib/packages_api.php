<?php

class rex_api_install_packages_download extends rex_api_install_packages_base
{
  const
    VERB = 'downloaded',
    SHOW_LINK = true;

  protected function checkPreConditions()
  {
    return !rex_addon::exists($this->addonkey);
  }

  protected function doAction()
  {
    $this->extractArchiveTo(rex_path::addon($this->addonkey));
    rex_package_manager::synchronizeWithFileSystem();
  }
}

class rex_api_install_packages_update extends rex_api_install_packages_base
{
  const
    VERB = 'updated',
    SHOW_LINK = false;

  private $addon;

  protected function checkPreConditions()
  {
    if(!rex_addon::exists($this->addonkey))
    {
      return false;
    }
    $this->addon = rex_addon::get($this->addonkey);
    rex_addon_manager::loadPackageInfos($this->addon);
    return rex_version_compare(rex_request('version', 'string'), $this->addon->getVersion(), '>');
  }

  public function doAction()
  {
    $temppath = rex_path::addon('_new_'. $this->addonkey);
    if(($msg = $this->extractArchiveTo($temppath)) !== true)
    {
      return $msg;
    }
    if($this->addon->isActivated() && file_exists($temppath .'package.yml'))
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
    $archivePath = rex_path::pluginData('install', 'packages', $this->addonkey .'/'. str_replace(array('/', '\\'), '_', $this->addon->getVersion()) .'/');
    rex_dir::create($archivePath);
    rename($path, $archivePath);
    rename($temppath, $path);
  }

  public function __destruct()
  {
    rex_dir::delete(rex_path::addon('_new_'. $this->addonkey));
  }
}

abstract class rex_api_install_packages_base extends rex_api_function
{
  protected
    $addonkey,
    $archive;

  public function execute()
  {
    $this->addonkey = rex_request('addonkey', 'string');
    if(!$this->checkPreConditions())
    {
      return null;
    }
    $archivefile = rex_install_webservice::getArchive(rex_request('file', 'string'));
    if(!$archivefile)
    {
      $message = rex_i18n::msg('install_packages_warning_zip_not_found');
    }
    else
    {
      $this->archive = "phar://$archivefile/". $this->addonkey;
      if(!file_exists($this->archive))
      {
        $message = rex_i18n::msg('install_packages_warning_zip_wrong_format');
      }
      else
      {
        $message = $this->doAction();
      }
      rex_file::delete($archivefile);
    }
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