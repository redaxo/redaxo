<?php

/**
 * @package redaxo\install
 *
 * @internal
 */
class rex_install_packages
{
    /**
     * @var array|null
     */
    private static $updatePackages;
    /**
     * @var array|null
     */
    private static $addPackages;
    /**
     * @var array|null
     */
    private static $myPackages;

    /**
     * @throws rex_functional_exception
     *
     * @return array
     */
    public static function getUpdatePackages()
    {
        if (is_array(self::$updatePackages)) {
            return self::$updatePackages;
        }

        self::$updatePackages = self::getPackages();

        foreach (self::$updatePackages as $key => $addon) {
            if (rex_addon::exists($key) && isset($addon['files'])) {
                self::unsetOlderVersions($key, rex_addon::get($key)->getVersion());
            } else {
                unset(self::$updatePackages[$key]);
            }
        }
        return self::$updatePackages;
    }

    public static function updatedPackage($package, $fileId)
    {
        self::unsetOlderVersions($package, self::$updatePackages[$package]['files'][$fileId]['version']);
    }

    private static function unsetOlderVersions($package, $version)
    {
        foreach (self::$updatePackages[$package]['files'] as $fileId => $file) {
            if (empty($version) || empty($file['version']) || rex_string::versionCompare($file['version'], $version, '<=')) {
                unset(self::$updatePackages[$package]['files'][$fileId]);
            }
        }
        if (empty(self::$updatePackages[$package]['files'])) {
            unset(self::$updatePackages[$package]);
        }
    }

    /**
     * Returns _all_ packages available on redaxo.org, including those already installed etc.
     *
     * @throws rex_functional_exception
     *
     * @return array
     */
    public static function getAddPackages()
    {
        if (is_array(self::$addPackages)) {
            return self::$addPackages;
        }

        self::$addPackages = self::getPackages();
        return self::$addPackages;
    }

    public static function addedPackage($package)
    {
        self::$myPackages = null;
    }

    /**
     * Returns all packages owned by the current user.
     *
     * @throws rex_functional_exception
     *
     * @return array
     */
    public static function getMyPackages()
    {
        if (is_array(self::$myPackages)) {
            return self::$myPackages;
        }

        self::$myPackages = self::getPackages('?only_my=1');
        foreach (self::$myPackages as $key => $addon) {
            if (!rex_addon::exists($key)) {
                unset(self::$myPackages[$key]);
            }
        }
        return self::$myPackages;
    }

    /**
     * @param string $path
     *
     * @return string
     */
    public static function getPath($path = '')
    {
        return 'packages/' . $path;
    }

    /**
     * @param string $path
     *
     * @throws rex_functional_exception
     *
     * @return array
     */
    private static function getPackages($path = '')
    {
        return rex_install_webservice::getJson(self::getPath($path));
    }

    /**
     * Deletes all locally cached packages.
     */
    public static function deleteCache()
    {
        self::$updatePackages = null;
        self::$addPackages = null;
        self::$myPackages = null;
        rex_install_webservice::deleteCache('packages/');
    }
}
