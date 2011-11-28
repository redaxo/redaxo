<?php

class rex_addon_manager extends rex_package_manager
{
  /**
   * Constructor
   *
   * @param rex_addon $addon Addon
   */
  protected function __construct(rex_addon $addon)
  {
    parent::__construct($addon, 'addon_');
  }

  /* (non-PHPdoc)
   * @see rex_package_manager::activate()
   */
  public function activate()
  {
    $state = parent::activate();

    if($state !== true)
      return $state;

    $plugins = new SplObjectStorage();

    foreach($this->package->getAvailablePlugins() as $plugin)
    {
      $plugins[$plugin] = rex_plugin_manager::factory($plugin);
      rex_plugin_manager::loadPackageInfos($plugin);
    }

    $deactivate = array();
    $finished = false;
    while(!$finished && !empty($plugins))
    {
      $finished = true;
      foreach($plugins as $plugin)
      {
        $pluginManager = $plugins[$plugin];
        $return = $pluginManager->checkRequirements();
        if(is_string($return) && !empty($return))
        {
          $plugin->setProperty('status', false);
          $deactivate[] = $pluginManager;
          $finished = false;
          unset($plugins[$plugin]);
        }
      }
    }

    foreach(array_reverse($deactivate) as $pluginManager)
    {
      $pluginManager->deactivate();
    }

    return $state;
  }

  /* (non-PHPdoc)
   * @see rex_package_manager::checkDependencies()
   */
  protected function checkDependencies()
  {
    $i18nPrefix = 'addon_dependencies_error_';
    $state = array();

    foreach(rex_addon::getAvailableAddons() as $addonName => $addon)
    {
      if($addon == $this->package)
        continue;

      $requirements = $addon->getProperty('requires', array());
      if(isset($requirements['addons'][$this->package->getName()]))
      {
        $state[] = rex_i18n::msg($i18nPrefix .'addon', $addonName);
      }

      // check if another Plugin which is installed, depends on the addon being un-installed
      foreach($addon->getAvailablePlugins() as $pluginName => $plugin)
      {
        $requirements = $plugin->getProperty('requires', array());
        if(isset($requirements['addons'][$this->package->getName()]))
        {
          $state[] = rex_i18n::msg($i18nPrefix .'plugin', $addonName, $pluginName);
        }
      }
    }

    return empty($state) ? true : implode('<br />', $state);
  }

	/* (non-PHPdoc)
	 * @see rex_package_manager::addToPackageOrder()
	 */
	protected function addToPackageOrder()
  {
    parent::addToPackageOrder();

    foreach($this->package->getAvailablePlugins() as $plugin)
    {
      $pluginManager = rex_plugin_manager::factory($plugin);
      $pluginManager->addToPackageOrder();
    }
  }

  /* (non-PHPdoc)
   * @see rex_package_manager::removeFromPackageOrder()
   */
  protected function removeFromPackageOrder()
  {
    parent::removeFromPackageOrder();

    foreach($this->package->getRegisteredPlugins() as $plugin)
    {
      $pluginManager = rex_plugin_manager::factory($plugin);
      $pluginManager->removeFromPackageOrder($plugin);
    }
  }
}