<?php

/**
 * Utility class to generate absolute paths
 *
 * @author gharlan
 *
 * @package redaxo5
 */
class rex_path
{
  static protected
    $relBase,
    $absBase,
    $backend;

  static public function init($htdocs, $backend)
  {
    self::$relBase = $htdocs;
    self::$absBase = realpath($htdocs) . '/';
    self::$backend = $backend;
  }

  /**
   * Returns a base path
   */
  static public function base($file = '')
  {
    return str_replace(array('/', '\\'), DIRECTORY_SEPARATOR, self::$absBase . $file);;
  }

  /**
   * Returns the path to the frontend
   */
  static public function frontend($file = '')
  {
    return static::base($file);
  }

  /**
   * Returns the path to the frontend-controller (index.php from frontend)
   */
  static public function frontendController()
  {
    return static::base('index.php');
  }

  /**
   * Returns the path to the backend
   */
  static public function backend($file = '')
  {
    return static::base(self::$backend . '/' . $file);
  }

  /**
   * Returns the path to the backend-controller (index.php from backend)
   */
  static public function backendController()
  {
    return static::backend('index.php');
  }

  /**
   * Returns the path to the media-folder
   */
  static public function media($file = '')
  {
    return static::base('media/' . $file);
  }

  /**
   * Returns the path to the assets folder of the core, which contains all assets required by the core to work properly.
   */
  static public function assets($file = '')
  {
    return static::base('assets/' . $file);
  }

  /**
   * Returns the path to the assets folder of the given addon, which contains all assets required by the addon to work properly.
   *
   * @see assets()
   */
  static public function addonAssets($addon, $file = '')
  {
    return static::assets('addons/' . $addon . '/' . $file);
  }

  /**
   * Returns the path to the assets folder of the given plugin of the given addon
   *
   * @see assets()
   */
  static public function pluginAssets($addon, $plugin, $file = '')
  {
    return static::addonAssets($addon, 'plugins/' . $plugin . '/' . $file);
  }

  /**
   * Returns the path to the data folder of the core.
   */
  static public function data($file = '')
  {
    return static::backend('data/' . $file);
  }

  /**
   * Returns the path to the data folder of the given addon.
   */
  static public function addonData($addon, $file = '')
  {
    return static::data('addons/' . $addon . '/' . $file);
  }

  /**
   * Returns the path to the data folder of the given plugin of the given addon.
   */
  static public function pluginData($addon, $plugin, $file = '')
  {
    return static::addonData($addon, 'plugins/' . $plugin . '/' . $file);
  }

  /**
   * Returns the path to the cache folder of the core
   */
  static public function cache($file = '')
  {
    return static::backend('cache/' . $file);
  }

  /**
   * Returns the path to the cache folder of the given addon.
   */
  static public function addonCache($addon, $file = '')
  {
    return static::cache('addons/' . $addon . '/' . $file);
  }

  /**
   * Returns the path to the cache folder of the given plugin
   */
  static public function pluginCache($addon, $plugin, $file = '')
  {
    return static::addonCache($addon, 'plugins/' . $plugin . '/' . $file);
  }

  /**
   * Returns the path to the src folder.
   */
  static public function src($file = '')
  {
    return static::backend('src/' . $file);
  }

  /**
   * Returns the path to the actual core
   */
  static public function core($file = '')
  {
    return static::src('core/' . $file);
  }

  /**
   * Returns the base path to the folder of the given addon
   */
  static public function addon($addon, $file = '')
  {
    return static::src('addons/' . $addon . '/' . $file);
  }

  /**
   * Returns the base path to the folder of the plugin of the given addon
   */
  static public function plugin($addon, $plugin, $file = '')
  {
    return static::addon($addon, 'plugins/' . $plugin . '/' . $file);
  }

  /**
   * Converts a relative path to an absolute
   *
   * @param string $relPath The relative path
   *
   * @return string Absolute path
   */
  static public function absolute($relPath)
  {
    $stack = array();

    // pfadtrenner vereinheitlichen
    $relPath = str_replace('\\', '/', $relPath);
    foreach (explode('/', $relPath) as $dir) {
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
