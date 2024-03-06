<?php

use Redaxo\Core\Translation\I18n;

/**
 * @internal
 *
 * @psalm-suppress MissingConstructor
 */
class rex_install_package_update extends rex_install_package_download
{
    /** @var rex_addon */
    private $addon;

    protected function getPackages()
    {
        return rex_install_packages::getUpdatePackages();
    }

    protected function checkPreConditions()
    {
        if (!rex_addon::exists($this->addonkey)) {
            throw new rex_functional_exception(sprintf('AddOn "%s" does not exist!', $this->addonkey));
        }
        $addon = rex_addon::get($this->addonkey);
        assert($addon instanceof rex_addon);
        $this->addon = $addon;
        if (!rex_version::compare($this->file['version'], $this->addon->getVersion(), '>')) {
            throw new rex_functional_exception(sprintf('Existing version of AddOn "%s" (%s) is newer than %s', $this->addonkey, $this->addon->getVersion(), $this->file['version']));
        }
    }

    public function doAction()
    {
        $path = rex_path::addon($this->addonkey);
        $temppath = rex_path::addon('.new.' . $this->addonkey);
        $oldVersion = $this->addon->getVersion();

        // remove temp dir very late otherwise Whoops could not find source files in case of errors
        register_shutdown_function(static fn () => rex_dir::delete($temppath));

        if (true !== ($msg = $this->extractArchiveTo($temppath))) {
            return $msg;
        }

        // ---- check package.yml
        $packageFile = $temppath . rex_addon::FILE_PACKAGE;
        if (!is_file($packageFile)) {
            return I18n::msg('package_missing_yml_file');
        }
        try {
            $config = rex_file::getConfig($packageFile);
        } catch (rex_yaml_parse_exception $e) {
            return I18n::msg('package_invalid_yml_file') . ' ' . $e->getMessage();
        }

        if ($this->addon->isAvailable() && true !== ($msg = $this->checkRequirements($config))) {
            return $msg;
        }

        // ---- include update.php
        if ($this->addon->isInstalled() && is_file($temppath . rex_addon::FILE_UPDATE)) {
            try {
                $this->addon->includeFile('../.new.' . $this->addonkey . '/' . rex_addon::FILE_UPDATE);
            } catch (rex_functional_exception $e) {
                return $e->getMessage();
            } catch (rex_sql_exception $e) {
                return 'SQL error: ' . $e->getMessage();
            }
            if ('' != ($msg = (string) $this->addon->getProperty('updatemsg', ''))) {
                return $msg;
            }
            if (!$this->addon->getProperty('update', true)) {
                return I18n::msg('package_no_reason');
            }
        }

        if ($this->addon->isInstalled() && isset($config['default_config'])) {
            foreach ($config['default_config'] as $key => $value) {
                if (!$this->addon->hasConfig($key)) {
                    $this->addon->setConfig($key, $value);
                }
            }
        }

        // ---- backup
        $assets = $this->addon->getAssetsPath();
        $installConfig = rex_file::getCache(rex_addon::get('install')->getDataPath('config.json'));
        if (isset($installConfig['backups']) && $installConfig['backups']) {
            $archivePath = rex_path::addonData('install', $this->addonkey . '/');
            rex_dir::create($archivePath);
            $archive = $archivePath . strtolower(preg_replace('/[^a-z0-9-_.]/i', '_', $this->addon->getVersion() ?: '0')) . '.zip';
            rex_install_archive::copyDirToArchive($path, $archive);
            if (is_dir($assets)) {
                rex_install_archive::copyDirToArchive($assets, $archive, 'assets');
            }
        }

        // ---- update main addon dir
        $pathOld = rex_path::addon($this->addonkey . '.old');
        error_clear_last();
        // move current addon to temp path
        if (!@rename($path, $pathOld)) {
            $message = $path . ' could not be moved to ' . $pathOld;
            $message .= ($error = error_get_last()) ? ': ' . $error['message'] : '.';
            throw new rex_functional_exception($message);
        }
        // move new addon to main addon path
        if (@rename($temppath, $path)) {
            // remove temp path of old addon
            rex_dir::delete($pathOld);
        } else {
            // revert to old addon
            rename($pathOld, $path);

            $message = $temppath . ' could not be moved to ' . $path;
            $message .= ($error = error_get_last()) ? ': ' . $error['message'] : '.';
            throw new rex_functional_exception($message);
        }

        // ---- update assets
        $origAssets = $this->addon->getPath('assets');
        if ($this->addon->isInstalled() && is_dir($origAssets)) {
            rex_dir::copy($origAssets, $assets);
        }

        // ---- update package order
        if ($this->addon->isAvailable()) {
            $this->addon->loadProperties(true);
            rex_addon_manager::generatePackageOrder();
        }

        $this->addon->setProperty('version', $this->file['version']);
        rex_install_packages::updatedPackage($this->addonkey, $this->fileId);

        rex_logger::factory()->info('AddOn ' . $this->addonkey . ' updated from ' . $oldVersion . ' to version ' . $this->file['version']);

        // re-generate opcache to make sure new/updated classes immediately are available
        if (function_exists('opcache_reset')) {
            opcache_reset();
        }

        return null;
    }

    /**
     * @return string|true
     */
    private function checkRequirements($config)
    {
        $temppath = rex_path::addon('.new.' . $this->addonkey);

        // ---- update "version", "requires" and "conflicts" properties
        /** @var SplObjectStorage<rex_addon, string> $versions */
        $versions = new SplObjectStorage();
        /** @var SplObjectStorage<rex_addon, array> $requirements */
        $requirements = new SplObjectStorage();
        /** @var SplObjectStorage<rex_addon, array> $conflicts */
        $conflicts = new SplObjectStorage();

        $requirements[$this->addon] = $this->addon->getProperty('requires', []);
        $this->addon->setProperty('requires', $config['requires'] ?? []);

        $conflicts[$this->addon] = $this->addon->getProperty('conflicts', []);
        $this->addon->setProperty('conflicts', $config['conflicts'] ?? []);

        $versions[$this->addon] = $this->addon->getVersion();
        $this->addon->setProperty('version', $config['version'] ?? $this->file['version']);

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
            foreach (rex_addon::getAvailableAddons() as $package) {
                if ($package->getAddon() === $this->addon) {
                    continue;
                }
                $manager = rex_addon_manager::factory($package);
                if (!$manager->checkPackageRequirement($this->addon->getPackageId())) {
                    $messages[] = $this->messageFromPackage($package, $manager);
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

        return empty($messages) ? true : '<ul><li>' . implode('</li><li>', $messages) . '</li></ul>';
    }

    private function messageFromPackage(rex_addon $package, rex_addon_manager $manager): string
    {
        return I18n::msg('install_warning_message_from_' . $package->getType(), $package->getPackageId()) . ' ' . $manager->getMessage();
    }
}
