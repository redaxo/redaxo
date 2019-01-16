<?php

/**
 * @package redaxo\install
 *
 * @internal
 */
class rex_api_install_package_add extends rex_api_install_package_download
{
    const VERB = 'downloaded';
    const SHOW_LINK = true;

    protected function getPackages()
    {
        return rex_install_packages::getAddPackages();
    }

    protected function checkPreConditions()
    {
        if (rex_addon::exists($this->addonkey)) {
            throw new rex_api_exception(sprintf('AddOn "%s" already exist!', $this->addonkey));
        }
    }

    protected function doAction()
    {
        if (($msg = $this->extractArchiveTo(rex_path::addon($this->addonkey))) !== true) {
            return $msg;
        }
        rex_package_manager::synchronizeWithFileSystem();
        rex_install_packages::addedPackage($this->addonkey);
    }
}
