<?php

use Redaxo\Core\Api\ApiException;
use Redaxo\Core\Api\ApiFunction;
use Redaxo\Core\Api\ApiResult;
use Redaxo\Core\Core;
use Redaxo\Core\Translation\I18n;

/**
 * @internal
 */
class rex_api_install_package_update extends ApiFunction
{
    public function execute()
    {
        if (Core::isLiveMode()) {
            throw new ApiException('Package management is not available in live mode!');
        }
        if (!Core::getUser()?->isAdmin()) {
            throw new ApiException('You do not have the permission!');
        }
        $addonkey = rex_request('addonkey', 'string');
        $fileId = rex_request('file', 'int');

        $installer = new rex_install_package_update();

        try {
            $message = $installer->run($addonkey, $fileId);
        } catch (rex_functional_exception $exception) {
            throw new ApiException($exception->getMessage());
        }

        if ($message) {
            $message = I18n::msg('install_warning_addon_not_updated', $addonkey) . '<br />' . $message;
            $success = false;
        } else {
            $message = I18n::msg('install_info_addon_updated', $addonkey);

            $success = true;
            unset($_REQUEST['addonkey']);
        }
        return new ApiResult($success, $message);
    }
}
