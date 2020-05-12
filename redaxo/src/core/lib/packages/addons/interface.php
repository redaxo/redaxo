<?php

/**
 * Interface for addons.
 *
 * @author gharlan
 *
 * @package redaxo\core\packages
 */
interface rex_addon_interface extends rex_package_interface
{
    /**
     * Returns the child plugin by the given name.
     *
     * @param string $plugin Name of the plugin
     *
     * @return rex_plugin_interface
     */
    public function getPlugin($plugin);

    /**
     * Returns if the plugin exists.
     *
     * @param string $plugin Name of the plugin
     *
     * @return bool
     */
    public function pluginExists($plugin);

    /**
     * Returns the registered plugins.
     *
     * @return rex_plugin[]
     * @psalm-return array<string, rex_plugin>
     */
    public function getRegisteredPlugins();

    /**
     * Returns the installed plugins.
     *
     * @return rex_plugin[]
     * @psalm-return array<string, rex_plugin>
     */
    public function getInstalledPlugins();

    /**
     * Returns the available plugins.
     *
     * @return rex_plugin[]
     * @psalm-return array<string, rex_plugin>
     */
    public function getAvailablePlugins();

    /**
     * Returns the system plugins.
     *
     * @return rex_plugin[]
     * @psalm-return array<string, rex_plugin>
     */
    public function getSystemPlugins();
}
