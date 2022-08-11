<?php

/**
 * @package redaxo\install
 *
 * @internal
 */
class rex_install_packages
{
    /** @var array<string, array{name: string, author: string, shortdescription: string, description: string, website: string, created: string, updated: string, files: array<int, array{version: string, description: string, path: string, checksum: string, created: string, updated: string}>}>|null */
    private static $updatePackages;
    /** @var array<string, array{name: string, author: string, shortdescription: string, description: string, website: string, created: string, updated: string, files: array<int, array{version: string, description: string, path: string, checksum: string, created: string, updated: string}>}>|null */
    private static $addPackages;
    /** @var array<string, array{name: string, author: string, shortdescription: string, description: string, website: string, created: string, updated: string, status: bool, files: array<int, array{version: string, description: string, path: string, checksum: string, created: string, updated: string, redaxo_versions: list<string>, status: bool}>}>|null */
    private static $myPackages;

    /**
     * @throws rex_functional_exception
     *
     * @return array<string, array{name: string, author: string, shortdescription: string, description: string, website: string, created: string, updated: string, files: array<int, array{version: string, description: string, path: string, checksum: string, created: string, updated: string}>}>
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

    /**
     * @param string $package
     * @param int $fileId
     * @return void
     */
    public static function updatedPackage($package, $fileId)
    {
        $updatePackages = self::getUpdatePackages();

        if (!isset($updatePackages[$package]['files'][$fileId]['version'])) {
            throw new RuntimeException(sprintf('List of updatable packages does not contain package "%s", or the package does not contain file "%s"', $package, $fileId));
        }

        self::unsetOlderVersions($package, $updatePackages[$package]['files'][$fileId]['version']);
    }

    /**
     * @param string $package
     * @param string $version
     * @return void
     */
    private static function unsetOlderVersions($package, $version)
    {
        assert(isset(self::$updatePackages[$package]['files']));
        foreach (self::$updatePackages[$package]['files'] as $fileId => $file) {
            if (empty($version) || empty($file['version']) || rex_version::compare($file['version'], $version, '<=')) {
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
     * @return array<string, array{name: string, author: string, shortdescription: string, description: string, website: string, created: string, updated: string, files: array<int, array{version: string, description: string, path: string, checksum: string, created: string, updated: string}>}>
     */
    public static function getAddPackages()
    {
        if (is_array(self::$addPackages)) {
            return self::$addPackages;
        }

        self::$addPackages = self::getPackages();
        return self::$addPackages;
    }

    /**
     * @return void
     */
    public static function addedPackage($package)
    {
        self::$myPackages = null;
    }

    /**
     * Returns all packages owned by the current user.
     *
     * @throws rex_functional_exception
     *
     * @return array<string, array{name: string, author: string, shortdescription: string, description: string, website: string, created: string, updated: string, status: bool, files: array<int, array{version: string, description: string, path: string, checksum: string, created: string, updated: string, redaxo_versions: list<string>, status: bool}>}>
     */
    public static function getMyPackages()
    {
        if (is_array(self::$myPackages)) {
            return self::$myPackages;
        }

        /** @var array<string, array{name: string, author: string, shortdescription: string, description: string, website: string, created: string, updated: string, status: bool, files: array<int, array{version: string, description: string, path: string, checksum: string, created: string, updated: string, redaxo_versions: list<string>, status: bool}>}> $myPackages */
        $myPackages = self::getPackages('?only_my=1');
        self::$myPackages = $myPackages;
        foreach (self::$myPackages as $key => $_) {
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
     * @return array<string, array{name: string, author: string, shortdescription: string, description: string, website: string, created: string, updated: string, status?: bool, files: array<int, array{version: string, description: string, path: string, checksum: string, created: string, updated: string, redaxo_versions?: list<string>, status?: bool}>}>
     *
     * @psalm-suppress MixedReturnTypeCoercion
     */
    private static function getPackages($path = '')
    {
        return rex_install_webservice::getJson(self::getPath($path));
    }

    /**
     * Deletes all locally cached packages.
     * @return void
     */
    public static function deleteCache()
    {
        self::$updatePackages = null;
        self::$addPackages = null;
        self::$myPackages = null;
        rex_install_webservice::deleteCache('packages/');
    }
}
