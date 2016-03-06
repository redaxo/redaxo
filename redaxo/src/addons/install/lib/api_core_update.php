<?php

/**
 * @package redaxo\install
 *
 * @internal
 */
class rex_api_install_core_update extends rex_api_function
{
    public static function getVersions()
    {
        return rex_install_webservice::getJson('core');
    }

    public function execute()
    {
        if (!rex::getUser()->isAdmin()) {
            throw new rex_api_exception('You do not have the permission!');
        }
        $installAddon = rex_addon::get('install');
        $versions = self::getVersions();
        $versionId = rex_request('version_id', 'int');
        if (!isset($versions[$versionId])) {
            return null;
        }
        $version = $versions[$versionId];
        if (!rex_string::versionCompare($version['version'], rex::getVersion(), '>')) {
            throw new rex_api_exception(sprintf('Existing version of Core (%s) is newer than %s', rex::getVersion(), $version['version']));
        }
        try {
            $archivefile = rex_install_webservice::getArchive($version['path']);
        } catch (rex_functional_exception $e) {
            throw new rex_api_exception($e->getMessage());
        }
        $message = '';
        $temppath = rex_path::coreCache('.new.core/');
        $coreAddons = [];
        /** @var rex_addon[] $updateAddons */
        $updateAddons = [];
        try {
            if ($version['checksum'] != md5_file($archivefile)) {
                throw new rex_functional_exception($installAddon->i18n('warning_zip_wrong_checksum'));
            }
            if (!rex_install_archive::extract($archivefile, $temppath)) {
                throw new rex_functional_exception($installAddon->i18n('warning_core_zip_not_extracted'));
            }
            if (!is_dir($temppath . 'core')) {
                throw new rex_functional_exception($installAddon->i18n('warning_zip_wrong_format'));
            }
            if (is_dir($temppath . 'addons')) {
                foreach (rex_finder::factory($temppath . 'addons')->dirsOnly() as $dir) {
                    $addonkey = $dir->getBasename();
                    $addonPath = $dir->getRealPath() . '/';
                    if (!file_exists($addonPath . rex_package::FILE_PACKAGE)) {
                        continue;
                    }

                    $config = rex_file::getConfig($addonPath . rex_package::FILE_PACKAGE);
                    if (
                        !isset($config['version']) ||
                        rex_addon::exists($addonkey) && rex_string::versionCompare($config['version'], rex_addon::get($addonkey)->getVersion(), '<')
                    ) {
                        continue;
                    }

                    $coreAddons[$addonkey] = $addonkey;
                    if (rex_addon::exists($addonkey)) {
                        $updateAddons[$addonkey] = rex_addon::get($addonkey);
                        $updateAddonsConfig[$addonkey] = $config;
                    }
                }
            }
            //$config = rex_file::getConfig($temppath . 'core/default.config.yml');
            //foreach ($config['system_addons'] as $addonkey) {
            //    if (is_dir($temppath . 'addons/' . $addonkey) && rex_addon::exists($addonkey)) {
            //        $updateAddons[$addonkey] = rex_addon::get($addonkey);
            //    }
            //}
            $this->checkRequirements($temppath, $version['version'], $updateAddonsConfig);
            if (file_exists($temppath . 'core/update.php')) {
                include $temppath . 'core/update.php';
            }
            foreach ($updateAddons as $addonkey => $addon) {
                if ($addon->isInstalled() && file_exists($file = $temppath . 'addons/' . $addonkey . '/' . rex_package::FILE_UPDATE)) {
                    try {
                        $addon->includeFile($file);
                        if ($msg = $addon->getProperty('updatemsg', '')) {
                            throw new rex_functional_exception($msg);
                        }
                        if (!$addon->getProperty('update', true)) {
                            throw new rex_functional_exception(rex_i18n::msg('package_no_reason'));
                        }
                    } catch (rex_functional_exception $e) {
                        throw new rex_functional_exception($addonkey . ': ' . $e->getMessage(), $e);
                    } catch (rex_sql_exception $e) {
                        throw new rex_functional_exception($addonkey . ': SQL error: ' . $e->getMessage(), $e);
                    }
                }
            }

            // create backup
            $installConfig = rex_file::getCache($installAddon->getDataPath('config.json'));
            if (isset($installConfig['backups']) && $installConfig['backups']) {
                rex_dir::create($installAddon->getDataPath());
                $archive = $installAddon->getDataPath(strtolower(preg_replace('/[^a-z0-9-_.]/i', '_', rex::getVersion())) . '.zip');
                rex_install_archive::copyDirToArchive(rex_path::core(), $archive);
                foreach ($updateAddons as $addonkey => $addon) {
                    rex_install_archive::copyDirToArchive($addon->getPath(), $archive, 'addons/' . $addonkey);
                }
            }

            // copy plugins to new addon dirs
            foreach ($updateAddons as $addonkey => $addon) {
                foreach ($addon->getRegisteredPlugins() as $plugin) {
                    $pluginPath = $temppath . 'addons/' . $addonkey . '/plugins/' . $plugin->getName();
                    if (!is_dir($pluginPath)) {
                        rex_dir::copy($plugin->getPath(), $pluginPath);
                    } elseif ($plugin->isInstalled() && is_dir($pluginPath . '/assets')) {
                        rex_dir::copy($pluginPath . '/assets', $plugin->getAssetsPath());
                    }
                }
            }

            // move temp dirs to permanent destination
            rex_dir::delete(rex_path::core());
            rename($temppath . 'core', rex_path::core());
            if (is_dir(rex_path::core('assets'))) {
                rex_dir::copy(rex_path::core('assets'), rex_path::coreAssets());
            }
            foreach ($coreAddons as $addonkey) {
                if (isset($updateAddons[$addonkey])) {
                    rex_dir::delete(rex_path::addon($addonkey));
                }
                rename($temppath . 'addons/' . $addonkey, rex_path::addon($addonkey));
                if (is_dir(rex_path::addon($addonkey, 'assets'))) {
                    rex_dir::copy(rex_path::addon($addonkey, 'assets'), rex_path::addonAssets($addonkey));
                }
            }
        } catch (rex_functional_exception $e) {
            $message = $e->getMessage();
        } catch (rex_sql_exception $e) {
            $message = 'SQL error: ' . $e->getMessage();
        }

        rex_file::delete($archivefile);
        rex_dir::delete($temppath);
        if ($message) {
            $message = $installAddon->i18n('warning_core_not_updated') . '<br />' . $message;
            $success = false;
        } else {
            $message = $installAddon->i18n('info_core_updated');
            $success = true;
            rex_delete_cache();
            rex_install_webservice::deleteCache('core');
            rex::setConfig('version', $version['version']);

            // ---- update package order
            foreach ($updateAddons as $addon) {
                if ($addon->isAvailable()) {
                    $addon->loadProperties();
                    foreach ($addon->getAvailablePlugins() as $plugin) {
                        $plugin->loadProperties();
                    }
                    rex_package_manager::generatePackageOrder();
                }
            }
        }

        $result = new rex_api_result($success, $message);
        if ($success) {
            $result->setRequiresReboot(true);
        }

        return $result;
    }

    /**
     * @param string      $temppath
     * @param string      $version
     * @param rex_addon[] $addons
     *
     * @throws rex_functional_exception
     */
    private function checkRequirements($temppath, $version, array $addons)
    {
        // ---- update "version", "requires" and "conflicts" properties
        $coreVersion = rex::getVersion();
        rex::setProperty('version', $version);
        $versions = new SplObjectStorage();
        $requirements = new SplObjectStorage();
        $conflicts = new SplObjectStorage();
        foreach ($addons as $addonkey => $config) {
            $addon = rex_addon::get($addonkey);
            $addonPath = $temppath . 'addons/' . $addonkey . '/';

            if (isset($config['requires'])) {
                $requirements[$addon] = $addon->getProperty('requires');
                $addon->setProperty('requires', $config['requires']);
            }
            if (isset($config['conflicts'])) {
                $conflicts[$addon] = $addon->getProperty('conflicts');
                $addon->setProperty('conflicts', $config['conflicts']);
            }
            $versions[$addon] = $addon->getVersion();
            $addon->setProperty('version', $config['version']);
            foreach ($addon->getAvailablePlugins() as $plugin) {
                if (is_dir($addonPath . 'plugins/' . $plugin->getName())) {
                    $config = rex_file::getConfig($addonPath . 'plugins/' . $plugin->getName() . '/' . rex_package::FILE_PACKAGE);
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
                        $plugin->setProperty('requires', $config['version']);
                    }
                }
            }
        }

        // ---- check requirements
        $messages = [];
        foreach (rex_package::getAvailablePackages() as $package) {
            $manager = rex_package_manager::factory($package);
            if (!$manager->checkRequirements()) {
                $messages[] = $package->getPackageId() . ': ' . $manager->getMessage();
            } elseif (!$manager->checkConflicts()) {
                $messages[] = $package->getPackageId() . ': ' . $manager->getMessage();
            }
        }

        // ---- reset "version", "requires" and "conflicts" properties
        rex::setProperty('version', $coreVersion);
        foreach ($versions as $package) {
            $package->setProperty('version', $versions[$package]);
        }
        foreach ($requirements as $package) {
            $package->setProperty('requires', $requirements[$package]);
        }
        foreach ($conflicts as $package) {
            $package->setProperty('conflicts', $conflicts[$package]);
        }

        if (!empty($messages)) {
            throw new rex_functional_exception(implode('<br />', $messages));
        }
    }
}
