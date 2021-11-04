<?php

/**
 * Represents a null package.
 *
 * Instances of this class are returned by `rex_package::get()` for non-existing packages.
 * Thereby it is safe to call `rex_package::get(...)->isAvailable()` and `isInstalled()`.
 * Other methods should not be called on null-packages since they do not return useful values.
 * Some methods like `getPath()` throw exceptions.
 *
 * @author gharlan
 *
 * @package redaxo\core\packages
 */
abstract class rex_null_package implements rex_package_interface
{
    use rex_singleton_trait;

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return static::class;
    }

    /**
     * {@inheritdoc}
     *
     * @return rex_null_addon
     */
    public function getAddon()
    {
        return rex_null_addon::getInstance();
    }

    /**
     * {@inheritdoc}
     */
    public function getPackageId()
    {
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function getPath($file = '')
    {
        throw new rex_exception(sprintf('Calling %s on %s is not allowed', __FUNCTION__, self::class));
    }

    /**
     * {@inheritdoc}
     */
    public function getAssetsPath($file = '')
    {
        throw new rex_exception(sprintf('Calling %s on %s is not allowed', __FUNCTION__, self::class));
    }

    /**
     * {@inheritdoc}
     */
    public function getAssetsUrl($file = '')
    {
        throw new rex_exception(sprintf('Calling %s on %s is not allowed', __FUNCTION__, self::class));
    }

    /**
     * {@inheritdoc}
     */
    public function getDataPath($file = '')
    {
        throw new rex_exception(sprintf('Calling %s on %s is not allowed', __FUNCTION__, self::class));
    }

    /**
     * {@inheritdoc}
     */
    public function getCachePath($file = '')
    {
        throw new rex_exception(sprintf('Calling %s on %s is not allowed', __FUNCTION__, self::class));
    }

    /**
     * {@inheritdoc}
     */
    public function setConfig($key, $value = null)
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function getConfig($key = null, $default = null)
    {
        return $default;
    }

    /**
     * {@inheritdoc}
     */
    public function hasConfig($key = null)
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function removeConfig($key)
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function setProperty($key, $value)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function getProperty($key, $default = null)
    {
        return $default;
    }

    /**
     * {@inheritdoc}
     */
    public function hasProperty($key)
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function removeProperty($key)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function isAvailable()
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function isInstalled()
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function isSystemPackage()
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function getAuthor($default = null)
    {
        return $default;
    }

    /**
     * {@inheritdoc}
     */
    public function getVersion($format = null)
    {
        return '';
    }

    /**
     * {@inheritdoc}
     */
    public function getSupportPage($default = null)
    {
        return $default;
    }

    /**
     * {@inheritdoc}
     */
    public function includeFile($file, array $context = [])
    {
    }

    /**
     * {@inheritdoc}
     */
    public function i18n($key, ...$replacements)
    {
        $args = func_get_args();
        return call_user_func_array([rex_i18n::class, 'msg'], $args);
    }
}
