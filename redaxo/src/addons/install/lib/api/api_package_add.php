<?php

use Redaxo\Core\Addon\Addon;
use Redaxo\Core\Addon\ApiFunction\Addon as ApiFunctionAddon;
use Redaxo\Core\ApiFunction\ApiFunction;
use Redaxo\Core\ApiFunction\ApiFunctionResult;
use Redaxo\Core\ApiFunction\Exception\ApiFunctionException;
use Redaxo\Core\Core;
use Redaxo\Core\Filesystem\Url;
use Redaxo\Core\Translation\I18n;

/**
 * @internal
 */
class rex_api_install_package_add extends ApiFunction
{
    public function execute()
    {
        if (Core::isLiveMode()) {
            throw new ApiFunctionException('Package management is not available in live mode!');
        }
        if (!Core::getUser()?->isAdmin()) {
            throw new ApiFunctionException('You do not have the permission!');
        }
        $addonkey = rex_request('addonkey', 'string');
        $fileId = rex_request('file', 'int');

        $installer = new rex_install_package_add();

        try {
            $message = $installer->run($addonkey, $fileId);
        } catch (rex_functional_exception $exception) {
            throw new ApiFunctionException($exception->getMessage());
        }

        if ($message) {
            $message = I18n::msg('install_warning_addon_not_downloaded', $addonkey) . '<br />' . $message;
            $success = false;
        } else {
            $package = Addon::get($addonkey);
            $packageInstallUrl = Url::currentBackendPage([
                'package' => $package->getPackageId(),
                'function' => 'install',
            ] + ApiFunctionAddon::getUrlParams());

            $message = I18n::msg('install_info_addon_downloaded', $addonkey)
                . ' <a href="' . Url::backendPage('packages', ['mark' => $addonkey]) . '">' . I18n::msg('install_to_addon_page') . '</a>'
                . ' | <a href="' . $packageInstallUrl . '">' . I18n::msg('install_to_addon_page_install') . '</a>';

            $success = true;
            unset($_REQUEST['addonkey']);
        }
        return new ApiFunctionResult($success, $message);
    }

    protected function requiresCsrfProtection()
    {
        return true;
    }
}
