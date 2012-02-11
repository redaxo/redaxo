<?php

class rex_plugin_manager extends rex_package_manager
{
  /**
   * Constructor
   *
   * @param rex_plugin $plugin Plugin
   */
  protected function __construct(rex_plugin $plugin)
  {
    parent::__construct($plugin, 'plugin_');
  }

  /* (non-PHPdoc)
   * @see rex_package_manager::checkDependencies()
   */
  public function checkDependencies()
  {
    $i18nPrefix = 'addon_dependencies_error_';
    $state = array();

    foreach(rex_addon::getAvailableAddons() as $availAddonName => $addon)
    {
      $requirements = $addon->getProperty('requires', array());
      if(isset($requirements['addons'][$this->package->getAddon()->getName()]['plugins'][$this->package->getName()]))
      {
        $state[] = rex_i18n::msg($i18nPrefix .'addon', $availAddonName);
      }

      // check if another Plugin which is installed, depends on the addon being un-installed
      foreach($addon->getAvailablePlugins() as $availPluginName => $plugin)
      {
        if($plugin == $this->package)
          continue;

        $requirements = $plugin->getProperty('requires', array());
        if(isset($requirements['addons'][$this->package->getAddon()->getName()]['plugins'][$this->package->getName()]))
        {
          $state[] = rex_i18n::msg($i18nPrefix .'plugin', $availAddonName, $availPluginName);
        }
      }
    }

    return empty($state) ? true : implode('<br />', $state);
  }
}
