<?php

/**
 * Represents a null package
 *
 * @author gharlan
 * @package redaxo\core
 */
abstract class rex_null_package implements rex_package_interface
{
    use rex_singleton_trait;

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return get_class($this);
    }

    /**
     * {@inheritDoc}
     */
    public function getAddon()
    {
        return rex_null_addon::getInstance();
    }

    /**
     * {@inheritDoc}
     */
    public function getPackageId()
    {
        return null;
    }

    /**
     * {@inheritDoc}
     */
    public function getPath($file = '')
    {
        return null;
    }

    /**
     * {@inheritDoc}
     */
    public function getAssetsPath($file = '')
    {
        return null;
    }

    /**
     * {@inheritDoc}
     */
    public function getAssetsUrl($file = '')
    {
        return null;
    }

    /**
     * {@inheritDoc}
     */
    public function getDataPath($file = '')
    {
        return null;
    }

    /**
     * {@inheritDoc}
     */
    public function getCachePath($file = '')
    {
        return null;
    }

    /**
     * {@inheritDoc}
     */
    public function setConfig($key, $value = null)
    {
        return false;
    }

    /**
     * {@inheritDoc}
     */
    public function getConfig($key = null, $default = null)
    {
        return $default;
    }

    /**
     * {@inheritDoc}
     */
    public function hasConfig($key = null)
    {
        return false;
    }

    /**
     * {@inheritDoc}
     */
    public function removeConfig($key)
    {
        return false;
    }

    /**
     * {@inheritDoc}
     */
    public function setProperty($key, $value)
    {
    }

    /**
     * {@inheritDoc}
     */
    public function getProperty($key, $default = null)
    {
        return $default;
    }

    /**
     * {@inheritDoc}
     */
    public function hasProperty($key)
    {
        return false;
    }

    /**
     * {@inheritDoc}
     */
    public function removeProperty($key)
    {
    }

    /**
     * {@inheritDoc}
     */
    public function isAvailable()
    {
        return false;
    }

    /**
     * {@inheritDoc}
     */
    public function isInstalled()
    {
        return false;
    }

    /**
     * {@inheritDoc}
     */
    public function isActivated()
    {
        return false;
    }

    /**
     * {@inheritDoc}
     */
    public function isSystemPackage()
    {
        return false;
    }

    /**
     * {@inheritDoc}
     */
    public function getAuthor($default = null)
    {
        return $default;
    }

    /**
     * {@inheritDoc}
     */
    public function getVersion($format = null)
    {
        return null;
    }

    /**
     * {@inheritDoc}
     */
    public function getSupportPage($default = null)
    {
        return $default;
    }

    /**
     * {@inheritDoc}
     */
    public function includeFile($file)
    {
    }

    /**
     * {@inheritDoc}
     */
    public function i18n($key)
    {
        $args = func_get_args();
        return call_user_func_array('rex_i18n::msg', $args);
    }
}
