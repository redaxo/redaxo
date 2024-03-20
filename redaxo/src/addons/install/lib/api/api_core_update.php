<?php

use Redaxo\Core\Core;
use Redaxo\Core\Filesystem\Dir;
use Redaxo\Core\Filesystem\File;
use Redaxo\Core\Filesystem\Finder;
use Redaxo\Core\Filesystem\Path;
use Redaxo\Core\Log\Logger;
use Redaxo\Core\Translation\I18n;
use Redaxo\Core\Util\Version;

/**
 * @internal
 */
class rex_api_install_core_update extends rex_api_function
{
    /**
     * @return array<int, array{version: string, description: string, path: string, checksum: string}>
     */
    public static function getVersions()
    {
        return rex_install_webservice::getJson('core');
    }

    public function execute()
    {
        if (Core::isLiveMode()) {
            throw new rex_api_exception('Core update is not available in live mode!');
        }
        if (!Core::getUser()?->isAdmin()) {
            throw new rex_api_exception('You do not have the permission!');
        }
        $installAddon = rex_addon::get('install');
        $versions = self::getVersions();
        $versionId = rex_request('version_id', 'int');

        if (!isset($versions[$versionId])) {
            throw new rex_api_exception('The requested core version can not be loaded, maybe it is already installed.');
        }
        $version = $versions[$versionId];
        if (!Version::compare($version['version'], Core::getVersion(), '>')) {
            throw new rex_api_exception(sprintf('Existing version of Core (%s) is newer than %s', Core::getVersion(), $version['version']));
        }
        if (!is_writable(Path::core())) {
            throw new rex_functional_exception($installAddon->i18n('warning_directory_not_writable', Path::core()));
        }
        try {
            $archivefile = rex_install_webservice::getArchive($version['path']);
        } catch (rex_functional_exception $e) {
            throw new rex_api_exception($e->getMessage());
        }

        // load logger class before core update to avoid getting logger class from new core while logging success message
        $logger = Logger::factory();

        $message = '';
        $temppath = Path::coreCache('.new.core/');
        $coreAddons = [];
        $updateAddons = [];
        $updateAddonsConfig = [];
        try {
            if ($version['checksum'] != md5_file($archivefile)) {
                throw new rex_functional_exception($installAddon->i18n('warning_zip_wrong_checksum'));
            }

            // remove temp dir very late otherwise Whoops could not find source files in case of errors
            register_shutdown_function(static fn () => Dir::delete($temppath));

            if (!rex_install_archive::extract($archivefile, $temppath)) {
                throw new rex_functional_exception($installAddon->i18n('warning_core_zip_not_extracted'));
            }
            if (!is_dir($temppath . 'core')) {
                throw new rex_functional_exception($installAddon->i18n('warning_zip_wrong_format'));
            }
            if (is_dir($temppath . 'addons')) {
                foreach (Finder::factory($temppath . 'addons')->dirsOnly() as $dir) {
                    $addonkey = $dir->getBasename();
                    $addonPath = $dir->getRealPath() . '/';
                    if (!is_file($addonPath . rex_addon::FILE_PACKAGE)) {
                        continue;
                    }

                    $config = File::getConfig($addonPath . rex_addon::FILE_PACKAGE);
                    if (
                        '' == $addonkey
                        || !isset($config['version'])
                        || rex_addon::exists($addonkey) && Version::compare($config['version'], rex_addon::get($addonkey)->getVersion(), '<')
                    ) {
                        continue;
                    }

                    $coreAddons[$addonkey] = $addonkey;
                    if (rex_addon::exists($addonkey)) {
                        $addon = rex_addon::get($addonkey);

                        if (!is_writable($addon->getPath())) {
                            throw new rex_functional_exception($installAddon->i18n('warning_directory_not_writable', $addon->getPath()));
                        }

                        $updateAddons[$addonkey] = $addon;
                        $updateAddonsConfig[$addonkey] = $config;
                    }
                }
            }
            // $config = File::getConfig($temppath . 'core/default.config.yml');
            // foreach ($config['system_addons'] as $addonkey) {
            //    if (is_dir($temppath . 'addons/' . $addonkey) && rex_addon::exists($addonkey)) {
            //        $updateAddons[$addonkey] = rex_addon::get($addonkey);
            //    }
            // }
            $this->checkRequirements($temppath, $version['version'], $updateAddonsConfig);
            if (is_file($temppath . 'core/update.php')) {
                include $temppath . 'core/update.php';
            }
            foreach ($updateAddons as $addonkey => $addon) {
                if ($addon->isInstalled() && is_file($file = $temppath . 'addons/' . $addonkey . '/' . rex_addon::FILE_UPDATE)) {
                    try {
                        $addon->includeFile($file);
                        if ($msg = $addon->getProperty('updatemsg', '')) {
                            throw new rex_functional_exception($msg);
                        }
                        if (!$addon->getProperty('update', true)) {
                            throw new rex_functional_exception(I18n::msg('package_no_reason'));
                        }
                    } catch (rex_functional_exception $e) {
                        throw new rex_functional_exception($addonkey . ': ' . $e->getMessage(), $e);
                    } catch (rex_sql_exception $e) {
                        throw new rex_functional_exception($addonkey . ': SQL error: ' . $e->getMessage(), $e);
                    }
                }

                if ($addon->isInstalled()) {
                    foreach ($addon->getProperty('default_config', []) as $key => $value) {
                        if (!$addon->hasConfig($key)) {
                            $addon->setConfig($key, $value);
                        }
                    }
                }
            }

            // create backup
            $installConfig = File::getCache($installAddon->getDataPath('config.json'));
            $pathCore = Path::core();
            if (isset($installConfig['backups']) && $installConfig['backups']) {
                Dir::create($installAddon->getDataPath());
                $archive = $installAddon->getDataPath(strtolower(preg_replace('/[^a-z0-9-_.]/i', '_', Core::getVersion())) . '.zip');
                rex_install_archive::copyDirToArchive($pathCore, $archive);
                foreach ($updateAddons as $addonkey => $addon) {
                    rex_install_archive::copyDirToArchive($addon->getPath(), $archive, 'addons/' . $addonkey);
                }
            }

            // move temp dirs to permanent destination
            error_clear_last();
            $pathOld = Path::src('core.old');
            // move current core to temp path
            if (!@rename($pathCore, $pathOld)) {
                $message = $pathCore . ' could not be moved to ' . $pathOld;
                $message .= ($error = error_get_last()) ? ': ' . $error['message'] : '.';
                throw new rex_functional_exception($message);
            }
            // move new core to main core path
            if (@rename($temppath . 'core', $pathCore)) {
                // remove temp path of old core
                Dir::delete($pathOld);
            } else {
                // revert to old core
                rename($pathOld, $pathCore);

                $message = $temppath . 'core could not be moved to ' . $pathCore;
                $message .= ($error = error_get_last()) ? ': ' . $error['message'] : '.';
                throw new rex_functional_exception($message);
            }

            if (is_dir(Path::core('assets'))) {
                Dir::copy(Path::core('assets'), Path::coreAssets());
            }
            foreach ($coreAddons as $addonkey) {
                if (isset($updateAddons[$addonkey])) {
                    $pathOld = Path::addon($addonkey . '.old');
                    // move whole old addon to a temp dir (high priority to get the free space for new addon version)
                    // and try to delete it afterwards (lower priority)
                    rename(Path::addon($addonkey), $pathOld);
                    Dir::delete($pathOld);
                }
                rename($temppath . 'addons/' . $addonkey, Path::addon($addonkey));
                if (is_dir(Path::addon($addonkey, 'assets'))) {
                    Dir::copy(Path::addon($addonkey, 'assets'), Path::addonAssets($addonkey));
                }
            }
        } catch (rex_functional_exception $e) {
            $message = $e->getMessage();
        } catch (rex_sql_exception $e) {
            $message = 'SQL error: ' . $e->getMessage();
        } finally {
            File::delete($archivefile);
        }

        if ($message) {
            $message = $installAddon->i18n('warning_core_not_updated') . '<br />' . $message;
            $success = false;
        } else {
            $logger->info('REDAXO Core updated from ' . Core::getVersion() . ' to version ' . $version['version']);

            $message = $installAddon->i18n('info_core_updated');
            $success = true;
            rex_delete_cache();
            rex_install_webservice::deleteCache();
            rex_install_packages::deleteCache();
            Core::setConfig('version', $version['version']);

            // ---- update package order
            /** @var rex_addon $addon */
            foreach ($updateAddons as $addon) {
                if ($addon->isAvailable()) {
                    $addon->loadProperties(true);
                }
            }
            rex_addon_manager::generatePackageOrder();

            // re-generate opcache to make sure new/updated classes immediately are available
            if (function_exists('opcache_reset')) {
                opcache_reset();
            }
        }

        $result = new rex_api_result($success, $message);
        if ($success) {
            $result->setRequiresReboot(true);
        }

        return $result;
    }

    protected function requiresCsrfProtection()
    {
        return true;
    }

    /**
     * @param string $temppath
     * @param string $version
     * @throws rex_functional_exception
     * @return void
     */
    private function checkRequirements($temppath, $version, array $addons)
    {
        // ---- update "version", "requires" and "conflicts" properties
        $coreVersion = Core::getVersion();
        Core::setProperty('version', $version);

        /** @var SplObjectStorage<rex_addon_interface, string> $versions */
        $versions = new SplObjectStorage();
        /** @var SplObjectStorage<rex_addon_interface, array> $requirements */
        $requirements = new SplObjectStorage();
        /** @var SplObjectStorage<rex_addon_interface, array> $conflicts */
        $conflicts = new SplObjectStorage();

        foreach ($addons as $addonkey => $config) {
            $addon = rex_addon::get($addonkey);
            $addonPath = $temppath . 'addons/' . $addonkey . '/';

            $requirements[$addon] = $addon->getProperty('requires', []);
            $addon->setProperty('requires', $config['requires'] ?? []);

            $conflicts[$addon] = $addon->getProperty('conflicts', []);
            $addon->setProperty('conflicts', $config['conflicts'] ?? []);

            $versions[$addon] = $addon->getVersion();
            $addon->setProperty('version', $config['version']);
        }

        // ---- check requirements
        $messages = [];
        foreach (rex_addon::getAvailableAddons() as $package) {
            $manager = rex_addon_manager::factory($package);
            if (!$manager->checkRequirements()) {
                $messages[] = $this->messageFromPackage($package, $manager);
            } elseif (!$manager->checkConflicts()) {
                $messages[] = $this->messageFromPackage($package, $manager);
            }
        }

        // ---- reset "version", "requires" and "conflicts" properties
        Core::setProperty('version', $coreVersion);
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
            throw new rex_functional_exception('<ul><li>' . implode('</li><li>', $messages) . '</li></ul>');
        }
    }

    private function messageFromPackage(rex_addon $package, rex_addon_manager $manager): string
    {
        return I18n::msg('install_warning_message_from_addon', $package->getPackageId()) . ' ' . $manager->getMessage();
    }
}
