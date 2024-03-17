<?php

use Redaxo\Core\Addon\Addon;
use Redaxo\Core\Addon\AddonManager;
use Redaxo\Core\Exception\UserMessageException;
use Redaxo\Core\Filesystem\Path;

/**
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
        if (Addon::exists($this->addonkey)) {
            throw new UserMessageException(sprintf('AddOn "%s" already exist!', $this->addonkey));
        }
    }

    protected function doAction()
    {
        if (true !== ($msg = $this->extractArchiveTo(Path::addon($this->addonkey)))) {
            return $msg;
        }
        AddonManager::synchronizeWithFileSystem();
        rex_install_packages::deleteCacheMyPackages();

        return null;
    }
}
