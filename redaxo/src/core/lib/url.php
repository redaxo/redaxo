<?php

/**
 * Utility class to generate relative URLs
 *
 * @author gharlan
 *
 * @package redaxo5
 */
class rex_url extends rex_path
{
  /**
   * Returns a base url
   */
  static public function base($file = '')
  {
    return self::$relBase . $file;
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
   * Returns the url to the backend-controller (index.php from backend)
   */
  static public function backendController(array $params = array())
  {
    $query = http_build_query($params);
    $query = $query ? '?' . $query : '';
    return self::backend('index.php' . $query);
  }
}
