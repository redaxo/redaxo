<?php

/**
 * @package redaxo\install
 *
 * @internal
 *
 * @psalm-suppress MissingConstructor
 */
class rex_install_package_add extends rex_install_package_download
{
    protected function getPackages()
    {
        return rex_install_packages::getAddPackages();
    }

    protected function checkPreConditions()
    {
        if (rex_addon::exists($this->addonkey)) {
            throw new rex_functional_exception(sprintf('AddOn "%s" already exist!', $this->addonkey));
        }
    }

    protected function doAction()
    {
        if (true !== ($msg = $this->extractArchiveTo(rex_path::addon($this->addonkey)))) {
            return $msg;
        }
        rex_package_manager::synchronizeWithFileSystem();
        rex_install_packages::deleteCacheMyPackages();

        return null;
    }
}
