<?php

/**
 * Utility class to generate relative URLs.
 *
 * @author gharlan
 *
 * @package redaxo\core
 */
class rex_url
{
    protected static $pathprovider;

    /**
     * Initializes the class.
     *
     * @param mixed $pathprovider A path provider
     */
    public static function init($pathprovider)
    {
        self::$pathprovider = $pathprovider;
    }

    /**
     * Returns a base url.
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
     * Returns the url to the frontend.
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
     * Returns the url to the frontend-controller (index.php from frontend).
     *
     * @param array $params Params
     * @param bool  $escape Flag whether the argument separator "&" should be escaped (&amp;)
     *
     * @return string
     */
    public static function frontendController(array $params = [], $escape = true)
    {
        $query = rex_string::buildQuery($params, $escape ? '&amp;' : '&');
        $query = $query ? '?' . $query : '';
        return self::$pathprovider->frontendController() . $query;
    }

    /**
     * Returns the url to the backend.
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
     * Returns the url to the backend-controller (index.php from backend).
     *
     * @param array $params Params
     * @param bool  $escape Flag whether the argument separator "&" should be escaped (&amp;)
     *
     * @return string
     */
    public static function backendController(array $params = [], $escape = true)
    {
        $query = rex_string::buildQuery($params, $escape ? '&amp;' : '&');
        $query = $query ? '?' . $query : '';
        return self::$pathprovider->backendController() . $query;
    }

    /**
     * Returns the url to a backend page.
     *
     * @param string $page   Page
     * @param array  $params Params
     * @param bool   $escape Flag whether the argument separator "&" should be escaped (&amp;)
     *
     * @return string
     */
    public static function backendPage($page, array $params = [], $escape = true)
    {
        return self::backendController(array_merge(['page' => $page], $params), $escape);
    }

    /**
     * Returns the url to the current backend page.
     *
     * @param array $params Params
     * @param bool  $escape Flag whether the argument separator "&" should be escaped (&amp;)
     *
     * @return string
     */
    public static function currentBackendPage(array $params = [], $escape = true)
    {
        return self::backendPage(rex_be_controller::getCurrentPage(), $params, $escape);
    }

    /**
     * Returns the url to the media-folder.
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
     * Returns the url to the assets folder.
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
     * Returns the url to the assets folder of the core, which contains all assets required by the core to work properly.
     *
     * @param string $file File
     *
     * @return string
     */
    public static function coreAssets($file = '')
    {
        return self::$pathprovider->coreAssets($file);
    }

    /**
     * Returns the url to the assets folder of the given addon, which contains all assets required by the addon to work properly.
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
     * Returns the url to the assets folder of the given plugin of the given addon.
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
}
