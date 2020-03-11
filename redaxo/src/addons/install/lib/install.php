<?php

/**
 * @package redaxo\install
 */
class rex_install
{
    /**
     * Download an AddOn from redaxo.org.
     *
     * @param string $addonKey e.g. "yform"
     * @param string $version  e.g. "3.2.1"
     *
     * @throws rex_exception
     */
    public function downloadAddon(string $addonKey, string $version): void
    {
        if (rex_addon::exists($addonKey)) {
            throw new rex_exception(sprintf('AddOn "%s" already exists!', $addonKey));
        }

        $packages = rex_install_packages::getAddPackages();
        if (!isset($packages[$addonKey])) {
            throw new rex_exception(sprintf('AddOn "%s" does not exist!', $addonKey));
        }
        $package = $packages[$addonKey];
        $files = $package['files'];

        // search fileId by version
        $fileId = null;
        foreach ($files as $fId => $fileMeta) {
            if ($fileMeta['version'] !== $version) {
                continue;
            }
            $fileId = $fId;
            break;
        }

        if (!$fileId || !isset($files[$fileId])) {
            throw new rex_exception(sprintf('Version "%s" not found!', $version));
        }

        $install = new rex_install_package_add();
        $message = $install->run($addonKey, $fileId);

        if ('' !== $message) {
            throw new rex_exception($message);
        }
    }

    /**
     * Updates an AddOn from redaxo.org.
     *
     * @param string $addonKey e.g. "yform"
     * @param string $version  e.g. "3.2.1"
     *
     * @throws rex_exception
     */
    public function updateAddon(string $addonKey, string $version): void
    {
        if (!rex_addon::exists($addonKey)) {
            throw new rex_exception(sprintf('AddOn "%s" don\'t exists!', $addonKey));
        }

        $packages = rex_install_packages::getUpdatePackages();

        if (!isset($packages[$addonKey])) {
            throw new rex_exception(sprintf('No Updates available for AddOn "%s"!', $addonKey));
        }
        $package = $packages[$addonKey];
        $files = $package['files'];

        // search fileId by version
        $fileId = null;
        foreach ($files as $fId => $fileMeta) {
            if ($fileMeta['version'] !== $version) {
                continue;
            }
            $fileId = $fId;
            break;
        }

        if (!$fileId || !isset($files[$fileId])) {
            throw new rex_exception(sprintf('Version "%s" does not exist or is below the current version!', $version));
        }

        $install = new rex_install_package_update();
        $message = $install->run($addonKey, $fileId);

        if ('' !== $message) {
            throw new rex_exception($message);
        }
    }
}
