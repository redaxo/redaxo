<?php

namespace Redaxo\Core\Filesystem;

use Redaxo\Core\Util\Str;
use rex_be_controller;

/**
 * Utility class to generate relative URLs.
 *
 * @psalm-import-type TUrlParams from Str
 */
final class Url
{
    private static DefaultPathProvider $pathprovider;

    private function __construct() {}

    /**
     * Initializes the class.
     */
    public static function init(DefaultPathProvider $pathProvider): void
    {
        self::$pathprovider = $pathProvider;
    }

    /**
     * Returns a base url.
     *
     * @return non-empty-string
     */
    public static function base(string $file = ''): string
    {
        return self::$pathprovider->base($file);
    }

    /**
     * Returns the url to the frontend.
     *
     * @return non-empty-string
     */
    public static function frontend(string $file = ''): string
    {
        return self::$pathprovider->frontend($file);
    }

    /**
     * Returns the url to the frontend-controller (index.php from frontend).
     *
     * @param TUrlParams $params
     * @return non-empty-string
     */
    public static function frontendController(array $params = []): string
    {
        $query = Str::buildQuery($params);
        $query = $query ? '?' . $query : '';
        return self::$pathprovider->frontendController() . $query;
    }

    /**
     * Returns the url to the backend.
     *
     * @return non-empty-string
     */
    public static function backend(string $file = ''): string
    {
        return self::$pathprovider->backend($file);
    }

    /**
     * Returns the url to the backend-controller (index.php from backend).
     *
     * @param TUrlParams $params
     * @return non-empty-string
     */
    public static function backendController(array $params = []): string
    {
        $query = Str::buildQuery($params);
        $query = $query ? '?' . $query : '';
        return self::$pathprovider->backendController() . $query;
    }

    /**
     * Returns the url to a backend page.
     *
     * @param TUrlParams $params
     * @return non-empty-string
     */
    public static function backendPage(string $page, array $params = []): string
    {
        return self::backendController(array_merge(['page' => $page], $params));
    }

    /**
     * Returns the url to the current backend page.
     *
     * @param TUrlParams $params
     * @return non-empty-string
     */
    public static function currentBackendPage(array $params = []): string
    {
        return self::backendPage(rex_be_controller::getCurrentPage(), $params);
    }

    /**
     * Returns the url to the media-folder.
     *
     * @return non-empty-string
     */
    public static function media(string $file = ''): string
    {
        return self::$pathprovider->media($file);
    }

    /**
     * Returns the url to the assets folder.
     *
     * @return non-empty-string
     */
    public static function assets(string $file = ''): string
    {
        return self::$pathprovider->assets($file);
    }

    /**
     * Returns the url to the assets folder of the core, which contains all assets required by the core to work properly.
     *
     * @return non-empty-string
     */
    public static function coreAssets(string $file = ''): string
    {
        return self::$pathprovider->coreAssets($file);
    }

    /**
     * Returns the url to the assets folder of the given addon, which contains all assets required by the addon to work properly.
     *
     * @param non-empty-string $addon
     * @return non-empty-string
     */
    public static function addonAssets(string $addon, string $file = ''): string
    {
        return self::$pathprovider->addonAssets($addon, $file);
    }
}
