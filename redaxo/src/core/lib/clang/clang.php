<?php

/**
 * Clang class
 *
 * @author gharlan
 */
class rex_clang
{
  static private
    $cacheLoaded = false,
    $clangs = array(),
    $current = 0;

  /**
   * Sets the current clang id
   *
   * @param integer $id Clang id
   */
  static public function setId($id)
  {
    if (!self::exists($id))
    {
      throw new rex_exception('Clang id "' . $id . '" doesn\'t exists');
    }
    self::$current = $id;
  }

  /**
   * Returns the current clang id
   *
   * @return integer Current clang id
   */
  static public function getId()
  {
    return self::$current;
  }

  /**
   * Checks if the given clang exists
   *
   * @param integer $id Clang id
   * @return boolean
   */
  static public function exists($id)
  {
    self::checkCache();
    return array_key_exists($id, self::$clangs);
  }

  /**
   * Returns the name for the current clang or the given id
   *
   * @param integer $id Clang id
   * @return string Clang name
   */
  static public function getName($id = null)
  {
    if ($id === null)
    {
      $id = self::getId();
    }
    if (!self::exists($id))
    {
      throw new rex_exception('Clang id "' . $id . '" doesn\'t exists');
    }
    return self::$clangs[$id];
  }

  /**
   * Counts the clangs
   *
   * @return integer
   */
  static public function count()
  {
    self::checkCache();
    return count(self::$clangs);
  }

  /**
   * Returns an array of all clang ids
   *
   * @return array
   */
  static public function getAllIds()
  {
    self::checkCache();
    return array_keys(self::$clangs);
  }

  /**
   * Returns an associative array (id => name) of all clangs
   *
   * @return array
   */
  static public function getAll()
  {
    self::checkCache();
    return self::$clangs;
  }

  /**
   * Loads the cache if not already loaded
   */
  static private function checkCache()
  {
    if (self::$cacheLoaded)
    {
      return;
    }

    $file = rex_path::cache('clang.cache');
    if (!file_exists($file))
    {
      rex_clang_service::generateCache();
    }
    if (file_exists($file))
    {
      self::$clangs = rex_file::getCache($file);
    }
    self::$cacheLoaded = true;
  }

  /**
   * Resets the intern cache of this class
   */
  static public function reset()
  {
    self::$cacheLoaded = false;
    self::$clangs = array();
  }
}
