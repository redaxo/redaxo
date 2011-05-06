<?php

/**
 * Interface for addons
 *
 * @author gharlan
 */
interface rex_addonInterface extends rex_packageInterface
{
  /**
   * Returns the child plugin by the given name
   *
   * @param string $plugin Name of the plugin
   *
   * @return rex_plugin
   */
  public function getPlugin($plugin);

	/**
   * Returns if the plugin exists
   *
   * @param string $plugin Name of the plugin
   *
   * @return boolean
   */
  public function pluginExists($plugin);

  /**
   * Returns the registered plugins
   *
   * @return array[rex_plugin]
   */
  public function getRegisteredPlugins();

  /**
   * Returns the installed plugins
   *
   * @return array[rex_plugin]
   */
  public function getInstalledPlugins();

	/**
   * Returns the available plugins
   *
   * @return array[rex_plugin]
   */
  public function getAvailablePlugins();
}