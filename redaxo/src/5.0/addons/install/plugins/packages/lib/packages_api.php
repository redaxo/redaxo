<?php

class rex_api_install_packages_download extends rex_api_function
{
  public function execute()
  {
    $addon = rex_request('addon', 'string');
    if(rex_addon::exists($addon))
    {
      return null;
    }
    $file = rex_request('file', 'string');
    $zip = rex_install_webservice::getZip($file);
    $message = '';
    if(!$zip)
    {
      $message = rex_i18n::msg('install_packages_warning_zip_not_found');
    }
    else
    {
      $list = $zip->getList();
      $base = current($list);
      if($base['file_name'] != $addon.'/')
      {
        $message = rex_i18n::msg('install_packages_warning_zip_wrong_format');
      }
      else
      {
        $zip->unzipAll(rex_path::version('addons/'), $addon);
        rex_package_manager::synchronizeWithFileSystem();
      }
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
    }
    return new rex_api_result($success, $message);
  }
}

class rex_api_install_packages_update extends rex_api_function
{
  public function execute()
  {
    return null;
    $addon = rex_request('addon', 'string');
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
    $file = rex_request('file', 'string');
    $zip = rex_install_webservice::getZip($file);
    $message = '';
    if(!$zip)
    {
      $message = rex_i18n::msg('install_packages_warning_zip_not_found');
    }
    else
    {
      $list = $zip->getList();
      $base = current($list);
      if($base['file_name'] != $addon.'/')
      {
        $message = rex_i18n::msg('install_packages_warning_zip_wrong_format');
      }
      else
      {
        $zip->unzipAll(rex_path::version('addons/'), $addon);
      }
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
    }
    return new rex_api_result($success, $message);
  }
}