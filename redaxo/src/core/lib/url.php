<?php

/**
 * Utility class to generate relative URLs
 *
 * @author gharlan
 *
 * @package redaxo5
 */
class rex_url
{
  static protected
    $base,
    $backend;

  static public function init($htdocs, $backend)
  {
    self::$base = $htdocs;
    self::$backend = $backend;
  }
  /**
   * Returns a base url
   */
  static public function base($file = '')
  {
    return self::$base . $file;
  }

  /**
   * Returns the url to the frontend
   */
  static public function frontend($file = '')
  {
    return self::base($file);
  }

  /**
   * Returns the url to the frontend-controller (index.php from frontend)
   */
  static public function frontendController(array $params = array())
  {
    $query = http_build_query($params);
    $query = $query ? '?' . $query : '';
    return self::base('index.php' . $query);
  }

  /**
   * Returns the url to the backend
   */
  static public function backend($file = '')
  {
    return self::base(self::$backend . '/' . $file);
  }

  /**
   * Returns the url to the backend-controller (index.php from backend)
   */
  static public function backendController(array $params = array())
  {
    $query = http_build_query($params);
    $query = $query ? '?' . $query : '';
    return self::backend('index.php' . $query);
  }

  /**
   * Returns the url to the media-folder
   */
  static public function media($file = '')
  {
    return self::base('media/' . $file);
  }

  /**
   * Returns the url to the assets folder of the core, which contains all assets required by the core to work properly.
   */
  static public function assets($file = '')
  {
    return self::base('assets/' . $file);
  }

  /**
   * Returns the url to the assets folder of the given addon, which contains all assets required by the addon to work properly.
   *
   * @see assets()
   */
  static public function addonAssets($addon, $file = '')
  {
    return self::assets('addons/' . $addon . '/' . $file);
  }

  /**
   * Returns the url to the assets folder of the given plugin of the given addon
   *
   * @see assets()
   */
  static public function pluginAssets($addon, $plugin, $file = '')
  {
    return self::addonAssets($addon, 'plugins/' . $plugin . '/' . $file);
  }
}
