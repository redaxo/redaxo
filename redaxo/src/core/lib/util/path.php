<?php

/**
 * Utility class to generate absolute paths.
 *
 * @author gharlan
 *
 * @package redaxo\core
 */
class rex_path
{
    protected static $base;
    protected static $backend;

    /**
     * Initializes the class.
     *
     * @param string $htdocs  Htdocs path
     * @param string $backend Backend folder name
     */
    public static function init($htdocs, $backend)
    {
        self::$base = realpath($htdocs) . '/';
        self::$backend = $backend;
    }

    /**
     * Returns a base path.
     *
     * @param string $file File
     *
     * @return string
     */
    public static function base($file = '')
    {
        return strtr(self::$base . $file, '/\\', DIRECTORY_SEPARATOR . DIRECTORY_SEPARATOR);
    }

    /**
     * Returns the path to the frontend.
     *
     * @param string $file File
     *
     * @return string
     */
    public static function frontend($file = '')
    {
        return self::base($file);
    }

    /**
     * Returns the path to the frontend-controller (index.php from frontend).
     *
     * @return string
     */
    public static function frontendController()
    {
        return self::base('index.php');
    }

    /**
     * Returns the path to the backend.
     *
     * @param string $file File
     *
     * @return string
     */
    public static function backend($file = '')
    {
        return self::base(self::$backend . '/' . $file);
    }

    /**
     * Returns the path to the backend-controller (index.php from backend).
     *
     * @return string
     */
    public static function backendController()
    {
        return self::backend('index.php');
    }

    /**
     * Returns the path to the media-folder.
     *
     * @param string $file File
     *
     * @return string
     */
    public static function media($file = '')
    {
        return self::base('media/' . $file);
    }

    /**
     * Returns the path to the assets folder of the core, which contains all assets required by the core to work properly.
     *
     * @param string $file File
     *
     * @return string
     */
    public static function assets($file = '')
    {
        return self::base('assets/' . $file);
    }

    /**
     * Returns the path to the assets folder of the given addon, which contains all assets required by the addon to work properly.
     *
     * @param string $addon Addon
     * @param string $file  File
     *
     * @return string
     *
     * @see assets()
     */
    public static function addonAssets($addon, $file = '')
    {
        return self::assets('addons/' . $addon . '/' . $file);
    }

    /**
     * Returns the path to the assets folder of the given plugin of the given addon.
     *
     * @param string $addon  Addon
     * @param string $plugin Plugin
     * @param string $file   File
     *
     * @return string
     *
     * @see assets()
     */
    public static function pluginAssets($addon, $plugin, $file = '')
    {
        return self::addonAssets($addon, 'plugins/' . $plugin . '/' . $file);
    }

    /**
     * Returns the path to the data folder of the core.
     *
     * @param string $file File
     *
     * @return string
     */
    public static function data($file = '')
    {
        return self::backend('data/' . $file);
    }

    /**
     * Returns the path to the data folder of the given addon.
     *
     * @param string $addon Addon
     * @param string $file  File
     *
     * @return string
     */
    public static function addonData($addon, $file = '')
    {
        return self::data('addons/' . $addon . '/' . $file);
    }

    /**
     * Returns the path to the data folder of the given plugin of the given addon.
     *
     * @param string $addon  Addon
     * @param string $plugin Plugin
     * @param string $file   File
     *
     * @return string
     */
    public static function pluginData($addon, $plugin, $file = '')
    {
        return self::addonData($addon, 'plugins/' . $plugin . '/' . $file);
    }

    /**
     * Returns the path to the cache folder of the core.
     *
     * @param string $file File
     *
     * @return string
     */
    public static function cache($file = '')
    {
        return self::backend('cache/' . $file);
    }

    /**
     * Returns the path to the cache folder of the given addon.
     *
     * @param string $addon Addon
     * @param string $file  File
     *
     * @return string
     */
    public static function addonCache($addon, $file = '')
    {
        return self::cache('addons/' . $addon . '/' . $file);
    }

    /**
     * Returns the path to the cache folder of the given plugin.
     *
     * @param string $addon  Addon
     * @param string $plugin Plugin
     * @param string $file   File
     *
     * @return string
     */
    public static function pluginCache($addon, $plugin, $file = '')
    {
        return self::addonCache($addon, 'plugins/' . $plugin . '/' . $file);
    }

    /**
     * Returns the path to the src folder.
     *
     * @param string $file File
     *
     * @return string
     */
    public static function src($file = '')
    {
        return self::backend('src/' . $file);
    }

    /**
     * Returns the path to the actual core.
     *
     * @param string $file File
     *
     * @return string
     */
    public static function core($file = '')
    {
        return self::src('core/' . $file);
    }

    /**
     * Returns the base path to the folder of the given addon.
     *
     * @param string $addon Addon
     * @param string $file  File
     *
     * @return string
     */
    public static function addon($addon, $file = '')
    {
        return self::src('addons/' . $addon . '/' . $file);
    }

    /**
     * Returns the base path to the folder of the plugin of the given addon.
     *
     * @param string $addon  Addon
     * @param string $plugin Plugin
     * @param string $file   File
     *
     * @return string
     */
    public static function plugin($addon, $plugin, $file = '')
    {
        return self::addon($addon, 'plugins/' . $plugin . '/' . $file);
    }

    /**
     * Converts a relative path to an absolute.
     *
     * @param string $relPath The relative path
     *
     * @return string Absolute path
     */
    public static function absolute($relPath)
    {
        $stack = [];

        // pfadtrenner vereinheitlichen
        $relPath = str_replace('\\', '/', $relPath);
        foreach (explode('/', $relPath) as $dir) {
            // Aktuelles Verzeichnis, oder Ordner ohne Namen
            if ($dir == '.' || $dir == '') {
                continue;
            }

            // Zum Parent
            if ($dir == '..') {
                array_pop($stack);
            }
            // Normaler Ordner
            else {
                array_push($stack, $dir);
            }
        }

        return implode(DIRECTORY_SEPARATOR, $stack);
    }
}
