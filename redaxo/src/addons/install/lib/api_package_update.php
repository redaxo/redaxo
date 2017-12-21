<?php

/**
 * @package redaxo\install
 *
 * @internal
 */
class rex_api_install_package_update extends rex_api_install_package_download
{
    const GET_PACKAGES_FUNCTION = 'getUpdatePackages';
    const VERB = 'updated';
    const SHOW_LINK = false;

    /**
     * @var rex_addon
     */
    private $addon;

    protected function checkPreConditions()
    {
        if (!rex_addon::exists($this->addonkey)) {
            throw new rex_api_exception(sprintf('AddOn "%s" does not exist!', $this->addonkey));
        }
        $this->addon = rex_addon::get($this->addonkey);
        if (!rex_string::versionCompare($this->file['version'], $this->addon->getVersion(), '>')) {
            throw new rex_api_exception(sprintf('Existing version of AddOn "%s" (%s) is newer than %s', $this->addonkey, $this->addon->getVersion(), $this->file['version']));
        }
    }

    public function doAction()
    {
        $path = rex_path::addon($this->addonkey);
        $temppath = rex_path::addon('.new.' . $this->addonkey);

        if (($msg = $this->extractArchiveTo($temppath)) !== true) {
            return $msg;
        }

        // ---- check package.yml
        $packageFile = $temppath . rex_package::FILE_PACKAGE;
        if (!file_exists($packageFile)) {
            return rex_i18n::msg('package_missing_yml_file');
        }
        try {
            $config = rex_file::getConfig($packageFile);
        } catch (rex_yaml_parse_exception $e) {
            return rex_i18n::msg('package_invalid_yml_file') . ' ' . $e->getMessage();
        }

        if ($this->addon->isAvailable() && ($msg = $this->checkRequirements($config)) !== true) {
            return $msg;
        }

        // ---- include update.php
        if ($this->addon->isInstalled() && file_exists($temppath . rex_package::FILE_UPDATE)) {
            try {
                $this->addon->includeFile('../.new.' . $this->addonkey . '/' . rex_package::FILE_UPDATE);
            } catch (rex_functional_exception $e) {
                return $e->getMessage();
            } catch (rex_sql_exception $e) {
                return 'SQL error: ' . $e->getMessage();
            }
            if (($msg = $this->addon->getProperty('updatemsg', '')) != '') {
                return $msg;
            }
            if (!$this->addon->getProperty('update', true)) {
                return rex_i18n::msg('package_no_reason');
            }
        }

        // ---- backup
        $assets = $this->addon->getAssetsPath();
        $installConfig = rex_file::getCache(rex_addon::get('install')->getDataPath('config.json'));
        if (isset($installConfig['backups']) && $installConfig['backups']) {
            $archivePath = rex_path::addonData('install', $this->addonkey . '/');
            rex_dir::create($archivePath);
            $archive = $archivePath . strtolower(preg_replace('/[^a-z0-9-_.]/i', '_', $this->addon->getVersion('0'))) . '.zip';
            rex_install_archive::copyDirToArchive($path, $archive);
            if (is_dir($assets)) {
                rex_install_archive::copyDirToArchive($assets, $archive, 'assets');
            }
        }

        // ---- copy plugins to new addon dir
        foreach ($this->addon->getRegisteredPlugins() as $plugin) {
            $pluginPath = $temppath . '/plugins/' . $plugin->getName();
            if (!is_dir($pluginPath)) {
                rex_dir::copy($plugin->getPath(), $pluginPath);
            } elseif ($plugin->isInstalled() && is_dir($pluginPath . '/assets')) {
                rex_dir::copy($pluginPath . '/assets', $plugin->getAssetsPath());
            }
        }

        // ---- update main addon dir
        rex_dir::delete($path);
        rename($temppath, $path);

        // ---- update assets
        $origAssets = $this->addon->getPath('assets');
        if ($this->addon->isInstalled() && is_dir($origAssets)) {
            rex_dir::copy($origAssets, $assets);
        }

        // ---- update package order
        if ($this->addon->isAvailable()) {
            $this->addon->loadProperties();
            foreach ($this->addon->getAvailablePlugins() as $plugin) {
                $plugin->loadProperties();
            }
            rex_package_manager::generatePackageOrder();
        }

        $this->addon->setProperty('version', $this->file['version']);
        rex_install_packages::updatedPackage($this->addonkey, $this->fileId);

        // re-generate opcache to make sure new/updated classes immediately are available
        if (function_exists('opcache_reset')) {
            opcache_reset();
        }
    }

    private function checkRequirements($config)
    {
        $temppath = rex_path::addon('.new.' . $this->addonkey);

        // ---- update "version", "requires" and "conflicts" properties
        $versions = new SplObjectStorage();
        $requirements = new SplObjectStorage();
        $conflicts = new SplObjectStorage();

        if (isset($config['requires'])) {
            $requirements[$this->addon] = $this->addon->getProperty('requires');
            $this->addon->setProperty('requires', $config['requires']);
        }
        if (isset($config['conflicts'])) {
            $conflicts[$this->addon] = $this->addon->getProperty('conflicts');
            $this->addon->setProperty('conflicts', $config['conflicts']);
        }

        $versions[$this->addon] = $this->addon->getVersion();
        $this->addon->setProperty('version', isset($config['version']) ? $config['version'] : $this->file['version']);
        $availablePlugins = $this->addon->getAvailablePlugins();
        foreach ($availablePlugins as $plugin) {
            if (is_dir($temppath . '/plugins/' . $plugin->getName())) {
                $config = rex_file::getConfig($temppath . '/plugins/' . $plugin->getName() . '/' . rex_package::FILE_PACKAGE);
                if (isset($config['requires'])) {
                    $requirements[$plugin] = $plugin->getProperty('requires');
                    $plugin->setProperty('requires', $config['requires']);
                }
                if (isset($config['conflicts'])) {
                    $conflicts[$plugin] = $plugin->getProperty('conflicts');
                    $plugin->setProperty('conflicts', $config['conflicts']);
                }
                if (isset($config['version'])) {
                    $versions[$plugin] = $plugin->getProperty('version');
                    $plugin->setProperty('version', $config['version']);
                }
            }
        }

        // ---- check requirements
        $messages = [];
        $manager = rex_addon_manager::factory($this->addon);
        if (!$manager->checkRequirements()) {
            $messages[] = $manager->getMessage();
        }
        if (!$manager->checkConflicts()) {
            $messages[] = $manager->getMessage();
        }

        if (empty($messages)) {
            foreach ($availablePlugins as $plugin) {
                $manager = rex_plugin_manager::factory($plugin);
                if (!$manager->checkRequirements()) {
                    $messages[] = $plugin->getPackageId() . ': ' . $manager->getMessage();
                }
                if (!$manager->checkConflicts()) {
                    $messages[] = $plugin->getPackageId() . ': ' . $manager->getMessage();
                }
            }
            foreach (rex_package::getAvailablePackages() as $package) {
                if ($package->getAddon() === $this->addon) {
                    continue;
                }
                $manager = rex_package_manager::factory($package);
                if (!$manager->checkPackageRequirement($this->addon->getPackageId())) {
                    $messages[] = $package->getPackageId() . ': ' . $manager->getMessage();
                } else {
                    foreach ($versions as $reqPlugin) {
                        if (!$manager->checkPackageRequirement($reqPlugin->getPackageId())) {
                            $messages[] = $package->getPackageId() . ': ' . $manager->getMessage();
                        }
                    }
                }
            }
        }

        // ---- reset "version", "requires" and "conflicts" properties
        foreach ($versions as $package) {
            $package->setProperty('version', $versions[$package]);
        }
        foreach ($requirements as $package) {
            $package->setProperty('requires', $requirements[$package]);
        }
        foreach ($conflicts as $package) {
            $package->setProperty('conflicts', $conflicts[$package]);
        }

        return empty($messages) ? true : implode('<br />', $messages);
    }

    public function __destruct()
    {
        rex_dir::delete(rex_path::addon('.new.' . $this->addonkey));
    }
}
