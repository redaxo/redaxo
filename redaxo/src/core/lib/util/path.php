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
    protected static $pathprovider;

    /**
     * Initializes the class.
     *
     * @param mixed $pathprovider  A path provider.
     */
    public static function init($pathprovider)
    {
        self::$pathprovider = $pathprovider;
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
        return self::$pathprovider->base($file);
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
        return self::$pathprovider->frontend($file);
    }

    /**
     * Returns the path to the frontend-controller (index.php from frontend).
     *
     * @return string
     */
    public static function frontendController()
    {
        return self::$pathprovider->frontendController();
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
        return self::$pathprovider->backend($file);
    }

    /**
     * Returns the path to the backend-controller (index.php from backend).
     *
     * @return string
     */
    public static function backendController()
    {
        return self::$pathprovider->backendController();
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
        return self::$pathprovider->media($file);
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
        return self::$pathprovider->assets($file);
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
        return self::$pathprovider->addonAssets($addon, $file);
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
        return self::$pathprovider->pluginAssets($addon, $plugin, $file);
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
        return self::$pathprovider->data($file);
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
        return self::$pathprovider->addonData($addon, $file);
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
        return self::$pathprovider->pluginData($addon, $plugin, $file);
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
        return self::$pathprovider->cache($file);
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
        return self::$pathprovider->addonCache($addon, $file);
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
        return self::$pathprovider->pluginCache($addon, $plugin, $file);
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
        return self::$pathprovider->src($file);
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
        return self::$pathprovider->core($file);
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
        return self::$pathprovider->addon($addon, $file);
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
        return self::$pathprovider->plugin($addon, $plugin, $file);
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
