<?php

/**
 * Represents a null addon
 *
 * @author gharlan
 * @package redaxo\core
 */
class rex_null_addon extends rex_null_package implements rex_addon_interface
{
    /**
     * {@inheritDoc}
     */
    public function getType()
    {
        return 'addon';
    }

    /**
     * {@inheritDoc}
     */
    public function getPlugin($plugin)
    {
        return rex_null_plugin::getInstance();
    }

    /**
     * {@inheritDoc}
     */
    public function pluginExists($plugin)
    {
        return false;
    }

    /**
     * {@inheritDoc}
     */
    public function getRegisteredPlugins()
    {
        return array();
    }

    /**
     * {@inheritDoc}
     */
    public function getInstalledPlugins()
    {
        return array();
    }

    /**
     * {@inheritDoc}
     */
    public function getAvailablePlugins()
    {
        return array();
    }

    /**
     * {@inheritDoc}
     */
    public function getSystemPlugins()
    {
        return array();
    }
}
