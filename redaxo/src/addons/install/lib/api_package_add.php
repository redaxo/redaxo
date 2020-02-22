<?php

/**
 * @package redaxo\install
 *
 * @internal
 */
class rex_api_install_package_add extends rex_api_install_package_download
{
    /**
     * @return string
     */
    protected function getErrorMessage()
    {
        return rex_i18n::msg('install_warning_addon_not_downloaded', $this->addonkey);
    }

    /**
     * @return string
     */
    protected function getSuccessMessage()
    {
        return rex_i18n::msg('install_info_addon_downloaded', $this->addonkey)
            . ' <a href="' . rex_url::backendPage('packages', ['mark' => $this->addonkey]) . '">' . rex_i18n::msg('install_to_addon_page') . '</a>';
    }

    /**
     * @return array
     */
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
        if (true !== ($msg = $this->extractArchiveTo(rex_path::addon($this->addonkey)))) {
            return $msg;
        }
        rex_package_manager::synchronizeWithFileSystem();
        rex_install_packages::addedPackage($this->addonkey);
    }
}
