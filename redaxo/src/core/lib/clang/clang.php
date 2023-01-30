<?php

/**
 * Clang class.
 *
 * @author gharlan
 *
 * @package redaxo\core
 */
#[AllowDynamicProperties]
class rex_clang
{
    /** @var bool */
    private static $cacheLoaded = false;
    /** @var self[] */
    private static $clangs = [];
    /** @var int|null */
    private static $currentId;

    /** @var int */
    private $id;
    /** @var string */
    private $code;
    /** @var string */
    private $name;
    /** @var int */
    private $priority;
    /** @var bool */
    private $status;

    private function __construct() {}

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
     * @return self|null
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
        throw new LogicException('No clang found.');
    }

    /**
     * Returns the current clang object.
     *
     * @return self
     */
    public static function getCurrent()
    {
        $clang = self::get(self::getCurrentId());

        if (!$clang) {
            throw new LogicException('Clang with id "' . self::getCurrentId() . '" not found.');
        }

        return $clang;
    }

    /**
     * Returns the current clang id.
     *
     * @return int Current clang id
     */
    public static function getCurrentId()
    {
        return self::$currentId ?? self::$currentId = self::getStartId();
    }

    /**
     * Sets the current clang id.
     *
     * @param int $id Clang id
     *
     * @throws rex_exception
     * @return void
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
        return $this->id;
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
     * @param string $key
     *
     * @return bool
     */
    public function hasValue($key)
    {
        return isset($this->$key) || isset($this->{'clang_' . $key});
    }

    /**
     * Returns the given value.
     *
     * @param string $key
     *
     * @return mixed
     */
    public function getValue($key)
    {
        if (isset($this->$key)) {
            return $this->$key;
        }

        if (isset($this->{'clang_' . $key})) {
            return $this->{'clang_' . $key};
        }

        return null;
    }

    /**
     * Counts the clangs.
     *
     * @param bool $ignoreOfflines
     *
     * @return int
     */
    public static function count($ignoreOfflines = false)
    {
        self::checkCache();
        return count(self::getAll($ignoreOfflines));
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

        return array_filter(self::$clangs, static function (self $clang) {
            return $clang->isOnline();
        });
    }

    /**
     * Loads the cache if not already loaded.
     * @return void
     */
    private static function checkCache()
    {
        if (self::$cacheLoaded) {
            return;
        }

        $file = rex_path::coreCache('clang.cache');
        if (!is_file($file)) {
            rex_clang_service::generateCache();
        }
        foreach (rex_file::getCache($file) as $id => $data) {
            $clang = new self();
            $clang->id = (int) $id;
            $clang->priority = (int) $data['priority'];
            $clang->status = (bool) $data['status'];

            foreach ($data as $key => $value) {
                if (in_array($key, ['id', 'priority', 'status'], true)) {
                    continue;
                }

                $clang->$key = $value;
            }

            self::$clangs[$id] = $clang;
        }
        self::$cacheLoaded = true;
    }

    /**
     * Resets the intern cache of this class.
     * @return void
     */
    public static function reset()
    {
        self::$cacheLoaded = false;
        self::$clangs = [];
    }
}
