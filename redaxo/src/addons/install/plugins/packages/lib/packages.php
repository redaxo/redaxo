<?php

class rex_install_packages
{
  static private
    $updatePackages,
    $addPackages,
    $myPackages;

  static public function getUpdatePackages()
  {
    if (is_array(self::$updatePackages))
      return self::$updatePackages;

    self::$updatePackages = self::getPackages();

    foreach (self::$updatePackages as $key => $addon) {
      if (rex_addon::exists($key) && isset($addon['files'])) {
        self::unsetOlderVersions($key, rex_addon::get($key)->getVersion());
      } else {
        unset(self::$updatePackages[$key]);
      }
    }
    return self::$updatePackages;
  }

  static public function updatedPackage($package, $fileId)
  {
    self::unsetOlderVersions($package, self::$updatePackages[$package]['files'][$fileId]['version']);
  }

  static private function unsetOlderVersions($package, $version)
  {
    foreach (self::$updatePackages[$package]['files'] as $fileId => $file) {
      if (empty($version) || empty($file['version']) || rex_string::compareVersions($file['version'], $version, '<=')) {
        unset(self::$updatePackages[$package]['files'][$fileId]);
      }
    }
    if (empty(self::$updatePackages[$package]['files'])) {
      unset(self::$updatePackages[$package]);
    }
  }

  static public function getAddPackages()
  {
    if (is_array(self::$addPackages))
      return self::$addPackages;

    self::$addPackages = self::getPackages();
    foreach (self::$addPackages as $key => $addon) {
      if (rex_addon::exists($key))
        unset(self::$addPackages[$key]);
    }
    return self::$addPackages;
  }

  static public function addedPackage($package)
  {
    unset(self::$addPackages[$package]);
  }

  static public function getMyPackages()
  {
    if (is_array(self::$myPackages))
      return self::$myPackages;

    self::$myPackages = self::getPackages('?only_my=1');
    return self::$myPackages;
  }

  static public function getPath($path = '')
  {
    return 'packages/' . $path;
  }

  static private function getPackages($path = '')
  {
    return rex_install_webservice::getJson(self::getPath($path));
  }

  static public function deleteCache()
  {
    self::$updatePackages = null;
    self::$addPackages = null;
    self::$myPackages = null;
    rex_install_webservice::deleteCache('packages/');
  }
}
