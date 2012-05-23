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
    $currentId = 0;

  private
    $id,
    $name;

  private function __construct($id, $name)
  {
    $this->id = $id;
    $this->name = $name;
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
   * Returns the clang object for the given id
   *
   * @param integer $id Clang id
   * @return self
   */
  static public function get($id)
  {
    if (self::exists($id))
    {
      return self::$clangs[$id];
    }
    return null;
  }

  /**
   * Returns the current clang object
   *
   * @return self
   */
  static public function getCurrent()
  {
    return self::get(self::getCurrentId());
  }

  /**
   * Returns the current clang id
   *
   * @return integer Current clang id
   */
  static public function getCurrentId()
  {
    return self::$currentId;
  }

  /**
   * Sets the current clang id
   *
   * @param integer $id Clang id
   */
  static public function setCurrentId($id)
  {
    if(!self::exists($id))
    {
      throw new rex_exception('Clang id "'. $id .'" doesn\'t exist');
    }
    self::$currentId = $id;
  }

  /**
   * Returns the id
   *
   * @return integer
   */
  public function getId()
  {
    return $this->id;
  }

  /**
   * Returns the name
   *
   * @return string
   */
  public function getName()
  {
    return $this->name;
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
   * Returns an array of all clangs
   *
   * @return array[self]
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
    if(self::$cacheLoaded)
    {
      return;
    }

    $file = rex_path::cache('clang.cache');
    if(!file_exists($file))
    {
      rex_clang_service::generateCache();
    }
    if(file_exists($file))
    {
      foreach(rex_file::getCache($file) as $id => $clang)
      {
        self::$clangs[$id] = new self($id, $clang['name']);
      }
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
