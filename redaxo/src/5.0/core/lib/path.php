<?php

/**
 * Utility class to generate relative and absolute path
 *
 * @author gharlan[at]web[dot]de Gregor Harlan
 *
 * @package redaxo5
 * @version svn:$Id$
 */
class rex_path
{
  const
    ABSOLUTE = 0,
    RELATIVE = 1;

  static private
    $relBase,
    $absBase,
    $backend,
    $version;

  static public function init($htdocs, $backend, $version)
  {
    self::$relBase = $htdocs;
    self::$absBase = realpath($htdocs) .'/';
    self::$backend = $backend;
    self::$version = $version;
  }

  /**
   * Returns the path to the frontend
   */
  static public function frontend($file = '', $pathType = self::RELATIVE)
  {
    return self::base($file, $pathType);
  }

  /**
   * Returns the path to the frontend-controller (index.php from frontend)
   */
  static public function frontendController($params = '')
  {
    return self::relBase('index.php'. $params);
  }

  /**
   * Returns the path to the backend
   */
  static public function backend($file = '', $pathType = self::RELATIVE)
  {
    return self::base(self::$backend .'/'. $file, $pathType);
  }

  /**
   * Returns the path to the backend-controller (index.php from backend)
   */
  static public function backendController($params = '')
  {
    return self::relBase(self::$backend .'index.php'. $params);
  }

  /**
   * Returns the path to the media-folder
   */
  static public function media($file = '', $pathType = self::RELATIVE)
  {
    return self::base('media/'. $file, $pathType);
  }

  /**
   * Returns the path to the assets folder of the core, which contains all assets required by the core to work properly.
   */
  static public function assets($file = '', $pathType = self::RELATIVE)
  {
    return self::base('assets/'. $file, $pathType);
  }

  /**
   * Returns the path to the assets folder of the given addon, which contains all assets required by the addon to work properly.
   *
   * @see #assets
   */
  static public function addonAssets($addon, $file = '', $pathType = self::RELATIVE)
  {
    return self::assets('addons/'. $addon .'/'. $file, $pathType);
  }

  /**
   * Returns the path to the assets folder of the given plugin of the given addon
   *
   * @see #assets
   */
  static public function pluginAssets($addon, $plugin, $file = '', $pathType = self::RELATIVE)
  {
    return self::addonAssets($addon, 'plugins/'. $plugin .'/'. $file, $pathType);
  }

  /**
   * Returns the path to the data folder of the core.
   */
  static public function data($file = '')
  {
    return self::absBase(self::$backend .'/data/'. $file);
  }

  /**
   * Returns the path to the data folder of the given addon.
   */
  static public function addonData($addon, $file = '')
  {
    return self::data('addons/'. $addon .'/'. $file);
  }

  /**
   * Returns the path to the data folder of the given plugin of the given addon.
   */
  static public function pluginData($addon, $plugin, $file = '')
  {
    return self::addonData($addon, 'plugins/'. $plugin .'/'. $file);
  }

  /**
   * Returns the path to the cache folder
   */
  static public function cache($file = '')
  {
    return self::absBase(self::$backend .'/cache/'. $file);
  }

  /**
   * Returns the path to the src folder.
   */
  static public function src($file = '')
  {
    return self::absBase(self::$backend .'/src/'. $file);
  }

  /**
   * Returns the path to the active version folder.
   *
   * There might be several version folders, but only one active.
   */
  static public function version($file = '')
  {
    return self::src(self::$version .'/'. $file);
  }

  /**
   * Returns the path to the actual core
   */
  static public function core($file = '')
  {
    return self::version('core/'. $file);
  }

  /**
   * Returns the base path to the folder of the given addon
   */
  static public function addon($addon, $file = '')
  {
    return self::version('addons/'. $addon .'/'. $file);
  }

  /**
   * Returns the base path to the folder of the plugin of the given addon
   */
  static public function plugin($addon, $plugin, $file = '')
  {
    return self::addon($addon, 'plugins/'. $plugin .'/'. $file);
  }

  /**
   * Returns a relative path
   */
  static private function relBase($file = '')
  {
    return self::$relBase . $file;
  }

  /**
   * Returns a absolute path
   */
  static private function absBase($file = '')
  {
    return str_replace(array('/', '\\'), DIRECTORY_SEPARATOR, self::$absBase . $file);
  }

  /**
   * Returns a base path
   */
  static private function base($file, $pathType = self::ABSOLUTE)
  {
    return $pathType == self::ABSOLUTE ? self::absBase($file) : self::relBase($file);
  }

  /**
   * Converts a relative path to an absolute
   *
   * @param string $relPath The relative path
   * @params boolean $relToCurrent When TRUE, the returned path is relative to the current directory
   *
   * @return string Absolute path
   */
  static public function absolute($relPath, $relToCurrent = false)
  {
    $stack = array();
    // Pfad relativ zum aktuellen Verzeichnis?
    // z.b. ../../media
    if($relToCurrent)
    {
      $path = realpath('.');
      $stack = explode(DIRECTORY_SEPARATOR, $path);
    }

    // pfadtrenner vereinheitlichen
    $relPath = str_replace('\\', '/', $relPath);
    foreach (explode('/', $relPath) as $dir)
    {
      // Aktuelles Verzeichnis, oder Ordner ohne Namen
      if ($dir == '.' || $dir == '')
        continue;

      // Zum Parent
      if ($dir == '..')
        array_pop($stack);
      // Normaler Ordner
      else
        array_push($stack, $dir);
    }

    return implode(DIRECTORY_SEPARATOR, $stack);
  }
}