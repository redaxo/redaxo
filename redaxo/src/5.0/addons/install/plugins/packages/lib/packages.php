<?php

class rex_install_packages
{
  static public function getAddAddons()
  {
    $addons = rex_install_webservice::getJson('addons');
    $array = array();
    foreach($addons as $addon)
    {
      if(!isset($array[$addon['addon_key']]) && !rex_addon::exists($addon['addon_key']))
        $array[$addon['addon_key']] = $addon;
    }
    ksort($array);
    return $array;
  }

  static public function getAddon($key)
  {
    $addon = rex_install_webservice::getJson('addons/?addonkey='.$key);
    return $addon;
  }

  static public function downloadAddon($addon, $path)
  {
    $zip = rex_install_webservice::getZip($path);
    if(!$zip)
    {
      return 'Die Zip-Datei konnte nicht heruntergeladen werden!';
    }
    $list = $zip->getList();
    $base = current($list);
    if($base['file_name'] != $addon.'/')
    {
      return 'Die Zip-Datei ist nicht im erforderlichen Format!';
    }
    $zip->unzipAll(rex_path::version('addons/'), $addon);
    rex_package_manager::synchronizeWithFileSystem();
    return true;
  }
}