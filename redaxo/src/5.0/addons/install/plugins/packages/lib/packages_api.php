<?php

class rex_api_install_packages_download extends rex_api_function
{
  public function execute()
  {
    $addon = rex_request('addonkey', 'string');
    if(rex_addon::exists($addon))
    {
      return null;
    }
    $message = '';
    $archive = rex_install_webservice::getArchive(rex_request('file', 'string'));
    if(!$archive)
    {
      $message = rex_i18n::msg('install_packages_warning_zip_not_found');
    }
    else
    {
      $path = "phar://$archive/$addon";
      if(!file_exists($path))
      {
        $message = rex_i18n::msg('install_packages_warning_zip_wrong_format');
      }
      else
      {
        rex_dir::copy($path, rex_path::addon($addon));
        rex_package_manager::synchronizeWithFileSystem();
      }
      rex_file::delete($archive);
    }
    if($message)
    {
      $message = rex_i18n::msg('install_packages_warning_not_downloaded', $addon) .'<br />'. $message;
      $success = false;
    }
    else
    {
      $message = rex_i18n::msg('install_packages_info_addon_downloaded', $addon) . ' <a href="index.php?page=addon">'. rex_i18n::msg('install_packages_to_addon_page') .'</a>';
      $success = true;
      unset($_REQUEST['addonkey']);
    }
    return new rex_api_result($success, $message);
  }
}

class rex_api_install_packages_update extends rex_api_function
{
  public function execute()
  {
    return null;
    $addon = rex_request('addonkey', 'string');
    if(!rex_adddon::exists($addon))
    {
      return null;
    }
    $addon = rex_addon::get($addon);
    rex_addon_manager::loadPackageInfos($addon);
    if(rex_version_compare(rex_request('version', 'string'), $addon->getVersion(), '<='))
    {
      return null;
    }
    $message = '';
    $archive = rex_install_webservice::getArchive(rex_request('file', 'string'));
    if(!$archive)
    {
      $message = rex_i18n::msg('install_packages_warning_zip_not_found');
    }
    else
    {
      $path = "phar://$archive/$addon";
      if(!file_exists($path))
      {
        $message = rex_i18n::msg('install_packages_warning_zip_wrong_format');
      }
      else
      {
        //TODO
      }
      rex_file::delete($archive);
    }
    if($message)
    {
      $message = rex_i18n::msg('install_packages_warning_not_updated', $addon) .'<br />'. $message;
      $success = false;
    }
    else
    {
      $message = rex_i18n::msg('install_packages_info_addon_updated', $addon);
      $success = true;
      unset($_REQUEST['addonkey']);
    }
    return new rex_api_result($success, $message);
  }
}