<?php

/**
 * Utility class to generate relative URLs
 *
 * @author gharlan
 *
 * @package redaxo\core
 */
class rex_url
{
    protected static $base;
    protected static $backend;

    /**
     * Initializes the class
     *
     * @param string $htdocs  Htdocs path
     * @param string $backend Backend folder name
     */
    public static function init($htdocs, $backend)
    {
        self::$base = $htdocs;
        self::$backend = substr($htdocs, -3) === '../' ? '' : $htdocs . $backend . '/';
    }
    /**
     * Returns a base url
     *
     * @param string $file File
     * @return string
     */
    public static function base($file = '')
    {
        return htmlspecialchars(self::$base . $file);
    }

    /**
     * Returns the url to the frontend
     *
     * @param string $file File
     * @return string
     */
    public static function frontend($file = '')
    {
        return self::base($file);
    }

    /**
     * Returns the url to the frontend-controller (index.php from frontend)
     *
     * @param array $params Params
     * @return string
     */
    public static function frontendController(array $params = [])
    {
        $query = rex_string::buildQuery($params);
        $query = $query ? '?' . $query : '';
        return self::base('index.php' . $query);
    }

    /**
     * Returns the url to the backend
     *
     * @param string $file File
     * @return string
     */
    public static function backend($file = '')
    {
        return htmlspecialchars(self::$backend . $file);
    }

    /**
     * Returns the url to the backend-controller (index.php from backend)
     *
     * @param array $params Params
     * @return string
     */
    public static function backendController(array $params = [])
    {
        $query = rex_string::buildQuery($params);
        $query = $query ? '?' . $query : '';
        return self::backend('index.php' . $query);
    }

    /**
     * Returns the url to a backend page
     *
     * @param string $page   Page
     * @param array  $params Params
     * @return string
     */
    public static function backendPage($page, array $params = [])
    {
        return self::backendController(array_merge(['page' => $page], $params));
    }

    /**
     * Returns the url to the current backend page
     *
     * @param array $params Params
     * @return string
     */
    public static function currentBackendPage(array $params = [])
    {
        return self::backendPage(rex_be_controller::getCurrentPage(), $params);
    }

    /**
     * Returns the url to the media-folder
     *
     * @param string $file File
     * @return string
     */
    public static function media($file = '')
    {
        return self::base('media/' . $file);
    }

    /**
     * Returns the url to the assets folder of the core, which contains all assets required by the core to work properly.
     *
     * @param string $file File
     * @return string
     */
    public static function assets($file = '')
    {
        return self::base('assets/' . $file);
    }

    /**
     * Returns the url to the assets folder of the given addon, which contains all assets required by the addon to work properly.
     *
     * @param string $addon Addon
     * @param string $file  File
     * @return string
     *
     * @see assets()
     */
    public static function addonAssets($addon, $file = '')
    {
        return self::assets('addons/' . $addon . '/' . $file);
    }

    /**
     * Returns the url to the assets folder of the given plugin of the given addon
     *
     * @param string $addon  Addon
     * @param string $plugin Plugin
     * @param string $file   File
     * @return string
     *
     * @see assets()
     */
    public static function pluginAssets($addon, $plugin, $file = '')
    {
        return self::addonAssets($addon, 'plugins/' . $plugin . '/' . $file);
    }
}
