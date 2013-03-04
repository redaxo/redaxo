<?php

/**
 * REDAXO Autoloader.
 *
 * This class was originally copied from the Symfony Framework:
 * Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * Adjusted in very many places
 *
 * @package redaxo\core
 */
class rex_autoload
{
    /**
     * @var Composer\Autoload\ClassLoader
     */
    protected static $composerLoader;

    protected static
        $registered   = false,
        $cacheFile    = null,
        $cacheChanged = false,
        $reloaded     = false,
        $dirs         = array(),
        $addedDirs    = array(),
        $classes      = array();

    /**
     * Register rex_autoload in spl autoloader.
     */
    public static function register()
    {
        if (self::$registered) {
            return;
        }

        ini_set('unserialize_callback_func', 'spl_autoload_call');

        if (!self::$composerLoader) {
            self::$composerLoader = require rex_path::core('vendor/autoload.php');
            // Unregister Composer Autoloader because we call self::$composerLoader->loadClass() manually
            self::$composerLoader->unregister();
        }

        if (false === spl_autoload_register(array(__CLASS__, 'autoload'))) {
            throw new Exception(sprintf('Unable to register %s::autoload as an autoloading method.', __CLASS__));
        }

        self::$cacheFile = rex_path::cache('autoload.cache');
        self::loadCache();
        register_shutdown_function(array(__CLASS__, 'saveCache'));

        self::$registered = true;
    }

    /**
     * Unregister rex_autoload from spl autoloader.
     */
    public static function unregister()
    {
        spl_autoload_unregister(array(__CLASS__, 'autoload'));
        self::$registered = false;
    }

    /**
     * Handles autoloading of classes.
     *
     * @param string $class A class name.
     *
     * @return boolean Returns true if the class has been loaded
     */
    public static function autoload($class)
    {
        // class already exists
        if (self::classExists($class)) {
            return true;
        }

        // we have a class path for the class, let's include it
        $lowerClass = strtolower($class);
        if (isset(self::$classes[$lowerClass]) && is_readable(self::$classes[$lowerClass])) {
            require_once self::$classes[$lowerClass];
        }

        // Return true if class exists now or if class exists after calling $composerLoader
        if (self::classExists($class) || self::$composerLoader->loadClass($class) && self::classExists($class)) {
            return true;
        } elseif (!self::$reloaded) {
            self::reload();
            return self::autoload($class);
        }

        return false;
    }

    private static function classExists($class)
    {
        return class_exists($class, false) || interface_exists($class, false) || trait_exists($class, false);
    }

    /**
     * Loads the cache.
     */
    private static function loadCache()
    {
        if (!self::$cacheFile || !is_readable(self::$cacheFile)) {
            return;
        }

        list(self::$classes, self::$dirs) = json_decode(file_get_contents(self::$cacheFile), true);
    }

    /**
     * Saves the cache.
     */
    public static function saveCache()
    {
        if (self::$cacheChanged) {
            if (is_writable(dirname(self::$cacheFile))) {
                file_put_contents(self::$cacheFile, json_encode(array(self::$classes, self::$dirs)));
                self::$cacheChanged = false;
            } else {
                throw new Exception("Unable to write autoload cachefile '" . self::$cacheFile . "'!");
            }
        }
    }

    /**
     * Reloads cache.
     */
    public static function reload()
    {
        self::$classes = array();
        self::$dirs = array();

        foreach (self::$addedDirs as $dir) {
            self::_addDirectory($dir);
        }

        self::$cacheChanged = true;
        self::$reloaded = true;
    }

    /**
     * Removes the cache.
     */
    public static function removeCache()
    {
        rex_file::delete(self::$cacheFile);
    }

    /**
     * Adds a directory to the autoloading system if not yet present and give it the highest possible precedence.
     *
     * @param string $dir The directory to look for classes
     */
    public static function addDirectory($dir)
    {
        $dir = rtrim($dir, '/\\') . DIRECTORY_SEPARATOR;
        self::$addedDirs[] = $dir;
        if (!in_array($dir, self::$dirs)) {
            self::_addDirectory($dir);
            self::$dirs[] = $dir;
            self::$cacheChanged = true;
        }
    }

    private static function _addDirectory($dir)
    {
        if (!is_dir($dir)) {
            return;
        }

        require_once rex_path::core('vendor/composer/ClassMapGenerator.php');

        foreach (Composer\Autoload\ClassMapGenerator::createMap($dir) as $class => $file) {
            $class = strtolower($class);
            if (!isset(self::$classes[$class])) {
                self::$classes[$class] = $file;
            }
        }
    }
}
