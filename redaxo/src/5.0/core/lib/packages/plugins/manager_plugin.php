<?php

class rex_pluginManager extends rex_packageManager
{
  static protected $class;

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
   * @see rex_packageManager::checkDependencies()
   */
  protected function checkDependencies()
  {
    global $REX;

    $i18nPrefix = 'addon_dependencies_error_';
    $state = array();

    foreach(rex_addon::getAvailableAddons() as $availAddonName => $addon)
    {
      $requirements = $addon->getProperty('requires', array());
      if(isset($requirements['addons']) && is_array($requirements['addons']))
      {
        foreach($requirements['addons'] as $addonName => $addonAttr)
        {
          if($addonName == $this->addonName && isset($addonAttr['plugins']) && is_array($addonAttr['plugins']))
          {
            foreach($addonAttr['plugins'] as $depName => $depAttr)
            {
              if($depName == $this->package->getName())
              {
                $state[] = rex_i18n::msg($i18nPrefix .'addon', $availAddonName);
              }
            }
          }
        }
      }

      // check if another Plugin which is installed, depends on the addon being un-installed
      foreach($addon->getAvailablePlugins() as $availPluginName => $plugin)
      {
        $requirements = $plugin->getProperty('requires', array());
        if(isset($requirements['addons']) && is_array($requirements['addons']))
        {
          foreach($requirements['addons'] as $addonName => $addonAttr)
          {
            if($addonName == $this->addonName && isset($addonAttr['plugins']) && is_array($addonAttr['plugins']))
            {
              foreach($addonAttr['plugins'] as $depName => $depAttr)
              {
                if($depName == $this->package->getName())
                {
                  $state[] = rex_i18n::msg($i18nPrefix .'plugin', $availAddonName, $availPluginName);
                }
              }
            }
          }
        }
      }
    }

    return empty($state) ? true : implode('<br />', $state);
  }
}