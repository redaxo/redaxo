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
      $message = rex_i18n::msg('install_packages_info_addon_downloaded', $addon)
               . ' <a class="rex-api-get" href="index.php?page=install&amp;subpage=packages&amp;subsubpage=add&amp;package='. $addon .'&amp;rex-api-call=install_packages_function&amp;function=install">'. ucfirst(rex_i18n::msg('addon_install')) .'</a>';
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
      $message = rex_i18n::msg('install_packages_info_addon_updated', $addon)
      . ' <a class="rex-api-get" href="index.php?page=install&amp;subpage=packages&amp;subsubpage=add&amp;package='. $addon .'&amp;rex-api-call=install_packages_function&amp;function=install">'. ucfirst(rex_i18n::msg('addon_install')) .'</a>';
      $success = true;
    }
    return new rex_api_result($success, $message);
  }
}

class rex_api_install_packages_function extends rex_api_package
{
  public function execute()
  {
    $function = rex_request('function', 'string');
    $package = rex_request('package', 'string');
    $result = parent::execute();
    $success = $result->isSuccessfull();
    $message = $result->getMessage();
    if($function == 'install' && $success)
    {
      $message .= ' <a class="rex-api-get" href="index.php?page=install&amp;subpage=packages&amp;subsubpage=add&amp;package='. $package .'&amp;rex-api-call=install_packages_function&amp;function=activate">'. ucfirst(rex_i18n::msg('addon_activate')) .'</a>';
    }
    return new rex_api_result($success, $message);
  }
}