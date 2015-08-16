<?php

/**
 * @package redaxo\core
 */
class rex_plugin_manager extends rex_package_manager
{
    /**
     * Constructor.
     *
     * @param rex_plugin $plugin Plugin
     */
    protected function __construct(rex_plugin $plugin)
    {
        parent::__construct($plugin, 'plugin_');
    }

    /**
     * {@inheritdoc}
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
}
