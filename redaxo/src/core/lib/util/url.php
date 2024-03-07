<?php

use Redaxo\Core\Filesystem\PathDefaultProvider;
/**
 * Utility class to generate relative URLs.
 */
class rex_url
{
    /** @var PathDefaultProvider */
    protected static $pathprovider;

    /**
     * Initializes the class.
     *
     * @param PathDefaultProvider $pathprovider A path provider
     * @return void
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
     * @return non-empty-string
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
     * @return non-empty-string
     */
    public static function frontend($file = '')
    {
        return self::$pathprovider->frontend($file);
    }

    /**
     * Returns the url to the frontend-controller (index.php from frontend).
     *
     * @param array $params Params
     * @return non-empty-string
     */
    public static function frontendController(array $params = [])
    {
        $query = rex_string::buildQuery($params);
        $query = $query ? '?' . $query : '';
        return self::$pathprovider->frontendController() . $query;
    }

    /**
     * Returns the url to the backend.
     *
     * @param string $file File
     *
     * @return non-empty-string
     */
    public static function backend($file = '')
    {
        return self::$pathprovider->backend($file);
    }

    /**
     * Returns the url to the backend-controller (index.php from backend).
     *
     * @param array $params Params
     * @return non-empty-string
     */
    public static function backendController(array $params = [])
    {
        $query = rex_string::buildQuery($params);
        $query = $query ? '?' . $query : '';
        return self::$pathprovider->backendController() . $query;
    }

    /**
     * Returns the url to a backend page.
     *
     * @param string $page Page
     * @param array $params Params
     * @return non-empty-string
     */
    public static function backendPage($page, array $params = [])
    {
        return self::backendController(array_merge(['page' => $page], $params));
    }

    /**
     * Returns the url to the current backend page.
     *
     * @param array $params Params
     * @return non-empty-string
     */
    public static function currentBackendPage(array $params = [])
    {
        return self::backendPage(rex_be_controller::getCurrentPage(), $params);
    }

    /**
     * Returns the url to the media-folder.
     *
     * @param string $file File
     *
     * @return non-empty-string
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
     * @return non-empty-string
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
     * @return non-empty-string
     */
    public static function coreAssets($file = '')
    {
        return self::$pathprovider->coreAssets($file);
    }

    /**
     * Returns the url to the assets folder of the given addon, which contains all assets required by the addon to work properly.
     *
     * @param string $addon Addon
     * @param string $file File
     *
     * @return non-empty-string
     *
     * @see assets()
     */
    public static function addonAssets($addon, $file = '')
    {
        return self::$pathprovider->addonAssets($addon, $file);
    }
}
