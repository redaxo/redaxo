<?php

/**
 * @package redaxo\install
 *
 * @internal
 */
class rex_api_install_package_add extends rex_api_install_package_download
{
    protected function getErrorMessage()
    {
        return rex_i18n::msg('install_warning_addon_not_downloaded', $this->addonkey);
    }

    protected function getSuccessMessage()
    {
        return rex_i18n::msg('install_info_addon_downloaded', $this->addonkey)
            . ' <a href="' . rex_url::backendPage('packages') . '">' . rex_i18n::msg('install_to_addon_page') . '</a>';
    }

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
