<?php

use Redaxo\Core\Addon\Addon;
use Redaxo\Core\Addon\ApiFunction\AddonOperation;
use Redaxo\Core\ApiFunction\ApiFunction;
use Redaxo\Core\ApiFunction\Exception\ApiFunctionException;
use Redaxo\Core\ApiFunction\Result;
use Redaxo\Core\Core;
use Redaxo\Core\Exception\UserMessageException;
use Redaxo\Core\Filesystem\Url;
use Redaxo\Core\Http\Request;
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
        $addonkey = Request::request('addonkey', 'string');
        $fileId = Request::request('file', 'int');

        $installer = new rex_install_package_add();

        try {
            $message = $installer->run($addonkey, $fileId);
        } catch (UserMessageException $exception) {
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
            ] + AddonOperation::getUrlParams());

            $message = I18n::msg('install_info_addon_downloaded', $addonkey)
                . ' <a href="' . Url::backendPage('packages', ['mark' => $addonkey]) . '">' . I18n::msg('install_to_addon_page') . '</a>'
                . ' | <a href="' . $packageInstallUrl . '">' . I18n::msg('install_to_addon_page_install') . '</a>';

            $success = true;
            unset($_REQUEST['addonkey']);
        }
        return new Result($success, $message);
    }

    protected function requiresCsrfProtection()
    {
        return true;
    }
}
