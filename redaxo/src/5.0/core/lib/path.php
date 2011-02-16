<?php

class rex_path
{
  static private
    $relBase,
    $absBase,
    $version;

  static public function init($htdocs, $version)
  {
    self::$relBase = $htdocs;
    self::$absBase = realpath($htdocs) .'/';
    self::$version = $version;
  }

  static public function frontend($file = '', $relative = false)
  {
    return self::base($file, $relative);
  }

  static public function frontendController($params = '')
  {
    return self::relBase('index.php'. $params);
  }

  static public function backend($file = '', $relative = false)
  {
    return self::base('redaxo/'. $file, $relative);
  }

  static public function backendController($params = '')
  {
    return self::relBase('redaxo/index.php'. $params);
  }

  static public function media($file = '', $relative = false)
  {
    return self::base('media/'. $file, $relative);
  }

  static public function assets($file = '', $relative = false)
  {
    return self::base('assets/'. $file, $relative);
  }

  static public function addonAssets($addon, $file = '', $relative = false)
  {
    return self::assets('addons/'. $addon .'/'. $file, $relative);
  }

  static public function pluginAssets($addon, $plugin, $file = '', $relative = false)
  {
    return self::addonAssets($addon, 'plugins/'. $plugin .'/'. $file, $relative);
  }

  static public function data($file = '')
  {
    return self::absBase('redaxo/data/'. $file);
  }

  static public function addonData($addon, $file = '')
  {
    return self::data('addons/'. $addon .'/'. $file);
  }

  static public function pluginData($addon, $plugin, $file = '')
  {
    return self::addonData($addon, 'plugins/'. $plugin .'/'. $file);
  }

  static public function generated($file = '')
  {
    return self::absBase('redaxo/generated/'. $file);
  }

  static public function src($file = '')
  {
    return self::absBase('redaxo/src/'. self::$version .'/'. $file);
  }

  static public function addon($addon, $file = '')
  {
    return self::src('addons/'. $addon .'/'. $file);
  }

  static public function plugin($addon, $plugin, $file = '')
  {
    return self::addon($addon, 'plugins/'. $plugin .'/'. $file);
  }

  static private function relBase($file = '')
  {
    return self::$relBase . $file;
  }

  static private function absBase($file = '')
  {
    return self::$absBase . $file;
  }

  static private function base($file, $relative = false)
  {
    return $relative ? self::relBase($file) : self::absBase($file);
  }
}