<?php

/**
 * Represents a null addon.
 *
 * @author gharlan
 *
 * @package redaxo\core\packages
 */
class rex_null_addon extends rex_null_package implements rex_addon_interface
{
    /**
     * {@inheritdoc}
     */
    public function getType()
    {
        return 'addon';
    }

    /**
     * {@inheritdoc}
     *
     * @return rex_null_plugin
     */
    public function getPlugin($plugin)
    {
        return rex_null_plugin::getInstance();
    }

    /**
     * {@inheritdoc}
     */
    public function pluginExists($plugin)
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function getRegisteredPlugins()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function getInstalledPlugins()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function getAvailablePlugins()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function getSystemPlugins()
    {
        return [];
    }
}
