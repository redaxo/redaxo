<?php

/**
 * @package redaxo\core\packages
 *
 * @psalm-property rex_addon $package
 */
class rex_addon_manager extends rex_package_manager
{
    /**
     * Constructor.
     *
     * @param rex_addon $addon Addon
     */
    protected function __construct(rex_addon $addon)
    {
        parent::__construct($addon, 'addon_');
    }

    /**
     * {@inheritdoc}
     */
    public function install($installDump = true)
    {
        $installed = $this->package->isInstalled();
        $this->generatePackageOrder = false;
        $return = parent::install($installDump);
        $this->generatePackageOrder = true;

        if (true === $return) {
            if (!$installed) {
                foreach ($this->package->getSystemPlugins() as $plugin) {
                    $manager = rex_plugin_manager::factory($plugin);
                    $manager->generatePackageOrder = false;
                    $manager->install();
                }
            }

            self::generatePackageOrder();
        }

        return $return;
    }

    /**
     * {@inheritdoc}
     */
    public function uninstall($installDump = true)
    {
        $isActivated = $this->package->isAvailable();
        if ($isActivated && !$this->deactivate()) {
            return false;
        }
        foreach ($this->package->getInstalledPlugins() as $plugin) {
            $plugin->setProperty('status', false);
            $manager = rex_plugin_manager::factory($plugin);
            if (!$manager->uninstall($installDump)) {
                $this->message = $manager->getMessage();
                return false;
            }
        }
        return parent::uninstall($installDump);
    }

    /**
     * {@inheritdoc}
     */
    public function activate()
    {
        $this->generatePackageOrder = false;
        $state = parent::activate();
        $this->generatePackageOrder = true;

        if (true !== $state) {
            return false;
        }

        $plugins = new SplObjectStorage();
        // create the managers for all available plugins
        foreach ($this->package->getAvailablePlugins() as $plugin) {
            $plugins[$plugin] = rex_plugin_manager::factory($plugin);
            $plugins[$plugin]->generatePackageOrder = false;
        }
        // mark all plugins whose requirements are not met
        // to consider dependencies among each other, iterate over all plugins until no plugin was marked in a round
        $deactivate = [];
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
     * {@inheritdoc}
     */
    public function checkDependencies()
    {
        $check = $addonCheck = parent::checkDependencies();
        $dependencies = [];
        foreach ($this->package->getAvailablePlugins() as $plugin) {
            $manager = rex_plugin_manager::factory($plugin);
            if (!$manager->checkDependencies()) {
                $dependencies[] = $manager->getMessage();
                $check = false;
            }
        }
        if (!empty($dependencies)) {
            if (!$addonCheck) {
                $this->message .= '<br />';
            }
            $this->message .= implode('<br />', $dependencies);
        }
        return $check;
    }

    /**
     * {@inheritdoc}
     */
    protected function wrongPackageId($addonName, $pluginName = null)
    {
        if (null !== $pluginName) {
            return $this->i18n('is_plugin', $addonName, $pluginName);
        }
        return $this->i18n('wrong_dir_name', $addonName);
    }
}
