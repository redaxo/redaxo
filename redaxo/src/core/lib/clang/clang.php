<?php

/**
 * Clang class
 *
 * @author gharlan
 * @package redaxo\core
 */
class rex_clang
{
    private static
        $cacheLoaded = false,
        $clangs = [],
        $currentId = 1;

    private
        $id,
        $code,
        $name;

    /**
     * Constructor
     *
     * @param integer $id   Clang id
     * @param string  $code Clang code
     * @param string  $name Clang name
     */
    private function __construct($id, $code, $name)
    {
        $this->id = $id;
        $this->code = $code;
        $this->name = $name;
    }

    /**
     * Checks if the given clang exists
     *
     * @param integer $id Clang id
     * @return boolean
     */
    public static function exists($id)
    {
        self::checkCache();
        return isset(self::$clangs[$id]);
    }

    /**
     * Returns the clang object for the given id
     *
     * @param integer $id Clang id
     * @return self
     */
    public static function get($id)
    {
        if (self::exists($id)) {
            return self::$clangs[$id];
        }
        return null;
    }

    /**
     * Returns the current clang object
     *
     * @return self
     */
    public static function getCurrent()
    {
        return self::get(self::getCurrentId());
    }

    /**
     * Returns the current clang id
     *
     * @return integer Current clang id
     */
    public static function getCurrentId()
    {
        return self::$currentId;
    }

    /**
     * Sets the current clang id
     *
     * @param integer $id Clang id
     * @throws rex_exception
     */
    public static function setCurrentId($id)
    {
        if (!self::exists($id)) {
            throw new rex_exception('Clang id "' . $id . '" doesn\'t exist');
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
     * Returns the lang code
     *
     * @return string
     */
    public function getCode()
    {
        return $this->code;
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
    public static function count()
    {
        self::checkCache();
        return count(self::$clangs);
    }

    /**
     * Returns an array of all clang ids
     *
     * @return int[]
     */
    public static function getAllIds()
    {
        self::checkCache();
        return array_keys(self::$clangs);
    }

    /**
     * Returns an array of all clangs
     *
     * @return self[]
     */
    public static function getAll()
    {
        self::checkCache();
        return self::$clangs;
    }

    /**
     * Loads the cache if not already loaded
     */
    private static function checkCache()
    {
        if (self::$cacheLoaded) {
            return;
        }

        $file = rex_path::cache('clang.cache');
        if (!file_exists($file)) {
            rex_clang_service::generateCache();
        }
        foreach (rex_file::getCache($file) as $id => $clang) {
            self::$clangs[$id] = new self($id, $clang['code'], $clang['name']);
        }
        self::$cacheLoaded = true;
    }

    /**
     * Resets the intern cache of this class
     */
    public static function reset()
    {
        self::$cacheLoaded = false;
        self::$clangs = [];
    }
}
