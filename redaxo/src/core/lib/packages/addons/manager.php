<?php

/**
 * @package redaxo\core
 */
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

    /**
     * {@inheritDoc}
     */
    public function install($installDump = true)
    {
        $installed = $this->package->isInstalled();
        $return = parent::install($installDump);

        if (!$installed && $return === true) {
            foreach ($this->package->getSystemPlugins() as $plugin) {
                $manager = rex_plugin_manager::factory($plugin);
                if ($manager->install() === true) {
                    $manager->activate();
                }
            }
        }

        return $return;
    }

    /**
     * {@inheritDoc}
     */
    public function activate()
    {
        $this->generatePackageOrder = false;
        $state = parent::activate();
        $this->generatePackageOrder = true;

        if ($state !== true) {
            return false;
        }

        $plugins = new SplObjectStorage;
        // create the managers for all available plugins
        foreach ($this->package->getAvailablePlugins() as $plugin) {
            $plugins[$plugin] = rex_plugin_manager::factory($plugin);
            $plugins[$plugin]->generatePackageOrder = false;
        }
        // mark all plugins whose requirements are not met
        // to consider dependencies among each other, iterate over all plugins until no plugin was marked in a round
        $deactivate = array();
        $finished = false;
        while (!$finished && count($plugins) > 0) {
            $finished = true;
            foreach ($plugins as $plugin) {
                $pluginManager = $plugins[$plugin];
                if (!$pluginManager->checkRequirements() || !$pluginManager->checkConflicts()) {
                    $plugin->setProperty('status', false);
                    $deactivate[] = $pluginManager;
                    $finished = false;
                    unset($plugins[$plugin]);
                }
            }
        }
        // deactivate all marked plugins
        foreach ($deactivate as $pluginManager) {
            $pluginManager->deactivate();
        }

        self::generatePackageOrder();

        return true;
    }

    /**
     * {@inheritDoc}
     */
    protected function wrongPackageId($addonName, $pluginName = null)
    {
        if ($pluginName !== null) {
            return $this->i18n('is_plugin', $addonName, $pluginName);
        }
        return $this->i18n('wrong_dir_name', $addonName);
    }
}
