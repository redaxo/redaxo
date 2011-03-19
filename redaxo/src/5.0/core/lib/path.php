<?php

class rex_path
{
  const
    RELATIVE = true,
    ABSOLUTE = false;
    
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

  static public function frontend($file = '', $pathType = rex_path::ABSOLUTE)
  {
    return self::base($file, $pathType);
  }

  static public function frontendController($params = '')
  {
    return self::relBase('index.php'. $params);
  }

  static public function backend($file = '', $pathType = rex_path::ABSOLUTE)
  {
    return self::base('redaxo/'. $file, $pathType);
  }

  static public function backendController($params = '')
  {
    return self::relBase('redaxo/index.php'. $params);
  }

  static public function media($file = '', $pathType = rex_path::ABSOLUTE)
  {
    return self::base('media/'. $file, $pathType);
  }

  static public function assets($file = '', $pathType = rex_path::ABSOLUTE)
  {
    return self::base('assets/'. $file, $pathType);
  }

  static public function addonAssets($addon, $file = '', $pathType = rex_path::ABSOLUTE)
  {
    return self::assets('addons/'. $addon .'/'. $file, $pathType);
  }

  static public function pluginAssets($addon, $plugin, $file = '', $pathType = rex_path::ABSOLUTE)
  {
    return self::addonAssets($addon, 'plugins/'. $plugin .'/'. $file, $pathType);
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
    return str_replace(array('/', '\\'), DIRECTORY_SEPARATOR, self::$absBase . $file);
  }

  static private function base($file, $pathType = rex_path::ABSOLUTE)
  {
    return $pathType == rex_path::ABSOLUTE ? self::absBase($file) : self::relBase($file);
  }
}