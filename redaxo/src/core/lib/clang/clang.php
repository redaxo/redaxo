<?php

/**
 * Clang class.
 *
 * @author gharlan
 *
 * @package redaxo\core
 */
class rex_clang
{
    private static $cacheLoaded = false;
    private static $clangs = [];
    private static $currentId = 1;

    private $id;
    private $code;
    private $name;
    private $priority;
    private $status;

    private function __construct()
    {
    }

    /**
     * Checks if the given clang exists.
     *
     * @param int $id Clang id
     *
     * @return bool
     */
    public static function exists($id)
    {
        self::checkCache();
        return isset(self::$clangs[$id]);
    }

    /**
     * Returns the clang object for the given id.
     *
     * @param int $id Clang id
     *
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
     * Returns the clang start id.
     *
     * @return int
     */
    public static function getStartId()
    {
        foreach (self::getAll() as $id => $clang) {
            return $id;
        }
    }

    /**
     * Returns the current clang object.
     *
     * @return self
     */
    public static function getCurrent()
    {
        return self::get(self::getCurrentId());
    }

    /**
     * Returns the current clang id.
     *
     * @return int Current clang id
     */
    public static function getCurrentId()
    {
        return self::$currentId;
    }

    /**
     * Sets the current clang id.
     *
     * @param int $id Clang id
     *
     * @throws rex_exception
     */
    public static function setCurrentId($id)
    {
        if (!self::exists($id)) {
            throw new rex_exception('Clang id "' . $id . '" doesn\'t exist');
        }
        self::$currentId = (int) $id;
    }

    /**
     * Returns the id.
     *
     * @return int
     */
    public function getId()
    {
        return (int) $this->id;
    }

    /**
     * Returns the lang code.
     *
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * Returns the name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Returns the priority.
     *
     * @return int
     */
    public function getPriority()
    {
        return $this->priority;
    }

    /**
     * Returns the status.
     *
     * @return bool
     */
    public function isOnline()
    {
        return $this->status;
    }

    /**
     * Checks whether the clang has the given value.
     *
     * @param string $value
     *
     * @return bool
     */
    public function hasValue($value)
    {
        return isset($this->$value) || isset($this->{'clang_' . $value});
    }

    /**
     * Returns the given value.
     *
     * @param string $value
     *
     * @return mixed
     */
    public function getValue($value)
    {
        if (isset($this->$value)) {
            return $this->$value;
        }

        if (isset($this->{'clang_' . $value})) {
            return $this->{'clang_' . $value};
        }

        return null;
    }

    /**
     * Counts the clangs.
     *
     * @return int
     */
    public static function count()
    {
        self::checkCache();
        return count(self::$clangs);
    }

    /**
     * Returns an array of all clang ids.
     *
     * @param bool $ignoreOfflines
     *
     * @return int[]
     */
    public static function getAllIds($ignoreOfflines = false)
    {
        self::checkCache();
        return array_keys(self::getAll($ignoreOfflines));
    }

    /**
     * Returns an array of all clangs.
     *
     * @param bool $ignoreOfflines
     *
     * @return self[]
     */
    public static function getAll($ignoreOfflines = false)
    {
        self::checkCache();

        if (!$ignoreOfflines) {
            return self::$clangs;
        }

        return array_filter(self::$clangs, function (self $clang) {
            return $clang->isOnline();
        });
    }

    /**
     * Loads the cache if not already loaded.
     */
    private static function checkCache()
    {
        if (self::$cacheLoaded) {
            return;
        }

        $file = rex_path::coreCache('clang.cache');
        if (!file_exists($file)) {
            rex_clang_service::generateCache();
        }
        foreach (rex_file::getCache($file) as $id => $data) {
            $clang = new self();

            foreach ($data as $key => $value) {
                $clang->$key = $value;
            }

            self::$clangs[$id] = $clang;
        }
        self::$cacheLoaded = true;
    }

    /**
     * Resets the intern cache of this class.
     */
    public static function reset()
    {
        self::$cacheLoaded = false;
        self::$clangs = [];
    }
}
