<?php

/**
 * Interface for packages.
 *
 * @author gharlan
 *
 * @package redaxo\core\packages
 */
interface rex_package_interface
{
    /**
     * Returns the name of the package.
     *
     * @return string Name
     */
    public function getName();

    /**
     * Returns the related Addon.
     *
     * @return rex_addon_interface
     */
    public function getAddon();

    /**
     * Returns the package ID.
     *
     * @return string|null
     */
    public function getPackageId();

    /**
     * Returns the package type as string.
     *
     * @return 'addon'|'plugin'
     */
    public function getType();

    /**
     * Returns the base path.
     *
     * @param string $file File
     * @return string
     */
    public function getPath($file = '');

    /**
     * Returns the assets path.
     *
     * @param string $file File
     * @return string
     */
    public function getAssetsPath($file = '');

    /**
     * Returns the assets url.
     *
     * @param string $file File
     * @return string
     */
    public function getAssetsUrl($file = '');

    /**
     * Returns the data path.
     *
     * @param string $file File
     * @return string
     */
    public function getDataPath($file = '');

    /**
     * Returns the cache path.
     *
     * @param string $file File
     * @return string
     */
    public function getCachePath($file = '');

    /**
     * @see rex_config::set()
     * @return bool
     */
    public function setConfig($key, $value = null);

    /**
     * @see rex_config::get()
     *
     * @template T as ?string
     * @psalm-param T $key
     * @psalm-return (T is string ? mixed|null : array<string, mixed>)
     * @return mixed
     */
    public function getConfig($key = null, $default = null);

    /**
     * @see rex_config::has()
     * @return bool
     */
    public function hasConfig($key = null);

    /**
     * @see rex_config::remove()
     * @return bool
     */
    public function removeConfig($key);

    /**
     * Sets a property.
     *
     * @param string $key   Key of the property
     * @param mixed  $value New value for the property
     * @return void
     */
    public function setProperty($key, $value);

    /**
     * Returns a property.
     *
     * @param string $key     Key of the property
     * @param mixed  $default Default value, will be returned if the property isn't set
     *
     * @return mixed
     */
    public function getProperty($key, $default = null);

    /**
     * Returns if a property is set.
     *
     * @param string $key Key of the property
     *
     * @return bool
     */
    public function hasProperty($key);

    /**
     * Removes a property.
     *
     * @param string $key Key of the property
     * @return void
     */
    public function removeProperty($key);

    /**
     * Returns if the package is available (activated and installed).
     *
     * @return bool
     */
    public function isAvailable();

    /**
     * Returns if the package is installed.
     *
     * @return bool
     */
    public function isInstalled();

    /**
     * Returns if it is a system package.
     *
     * @return bool
     */
    public function isSystemPackage();

    /**
     * Returns the author.
     *
     * @param string|null $default Default value, will be returned if the property isn't set
     *
     * @return string|null
     */
    public function getAuthor($default = null);

    /**
     * Returns the version.
     *
     * @param string $format See {@link rex_formatter::version()}
     *
     * @return string
     */
    public function getVersion($format = null);

    /**
     * Returns the supportpage.
     *
     * @param string|null $default Default value, will be returned if the property isn't set
     *
     * @return string|null
     */
    public function getSupportPage($default = null);

    /**
     * Includes a file in the package context.
     *
     * @param string $file    Filename
     * @param array  $context Context values, available as variables in given file
     * @return mixed
     */
    public function includeFile($file, array $context = []);

    /**
     * Adds the package prefix to the given key and returns the translation for it.
     *
     * @param string     $key             Key
     * @param string|int ...$replacements A arbritary number of strings used for interpolating within the resolved messag
     *
     * @return string Translation for the key
     */
    public function i18n($key, ...$replacements);
}
