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
   * @see rex_package_manager::wrongPackageId()
   */
  protected function wrongPackageId($addonName, $pluginName = null)
  {
    if ($pluginName === null) {
      return $this->i18n('is_addon', $addonName);
    }
    if ($addonName != $this->package->getAddon()->getName()) {
      return $this->i18n('is_plugin', $addonName, $pluginName);
    }
    return $this->i18n('wrong_dir_name', $pluginName);
  }

  /* (non-PHPdoc)
   * @see rex_package_manager::checkDependencies()
   */
  public function checkDependencies()
  {
    $i18nPrefix = 'package_dependencies_error_';
    $state = array();

    foreach (rex_package::getAvailablePackages() as $package) {
      if ($package === $this->package)
        continue;

      $requirements = $package->getProperty('requires', array());
      if (isset($requirements['addons'][$this->package->getAddon()->getName()]['plugins'][$this->package->getName()])) {
        $state[] = rex_i18n::msg($i18nPrefix . $package->getType(), $package->getAddon()->getName(), $package->getName());
      }
    }

    return empty($state) ? true : implode('<br />', $state);
  }
}
