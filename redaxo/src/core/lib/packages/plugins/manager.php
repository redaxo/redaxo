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

    foreach (rex_package::getAvailablePackages() as $package)
    {
      if ($package === $this->package)
        continue;

      $requirements = $package->getProperty('requires', array());
      if (isset($requirements['addons'][$this->package->getAddon()->getName()]['plugins'][$this->package->getName()]))
      {
        $state[] = rex_i18n::msg($i18nPrefix . $package->getType(), $package->getAddon()->getName(), $package->getName());
      }
    }

    return empty($state) ? true : implode('<br />', $state);
  }
}
