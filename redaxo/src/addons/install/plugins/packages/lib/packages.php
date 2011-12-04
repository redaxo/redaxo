<?php

class rex_install_packages
{
  static public function getUpdateAddons()
  {
    $addons = self::getAddons();

    foreach($addons as $key => $addon)
    {
      if(rex_addon::exists($key) && isset($addon['files']))
      {
        rex_addon_manager::loadPackageInfos(rex_addon::get($key));
        foreach($addon['files'] as $filekey => $file)
        {
          if(rex_version_compare($file['version'], rex_addon::get($key)->getVersion(), '>'))
          {
            $addons[$key]['available_versions'][] = $file['version'];
          }
          else
          {
            unset($addons[$key]['files'][$filekey]);
          }
        }
        if(empty($addons[$key]['files']))
        {
          unset($addons[$key]);
        }
      }
      else
      {
        unset($addons[$key]);
      }
    }
    return $addons;
  }

  static public function getAddAddons()
  {
    $addons = self::getAddons();
    foreach($addons as $key => $addon)
    {
      if(rex_addon::exists($key))
        unset($addons[$key]);
    }
    return $addons;
  }

  static public function getMyPackages()
  {
    $addons = self::getAddons();
    foreach($addons as $key => $addon)
    {
      if(!$addon['mine'] || !rex_addon::exists($key))
        unset($addons[$key]);
    }
    return $addons;
  }

  static private function getAddons()
  {
    $plugin = rex_plugin::get('install', 'packages');
    $path = 'addons/';
    $login = $plugin->getConfig('api_login');
    if($login)
    {
      $path .= '?api_login='. $login .'&api_key='. $plugin->getConfig('api_key');
    }
    return rex_install_webservice::getJson($path);
  }
}