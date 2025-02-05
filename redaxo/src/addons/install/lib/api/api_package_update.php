<?php

use Redaxo\Core\ApiFunction\ApiFunction;
use Redaxo\Core\ApiFunction\Exception\ApiFunctionException;
use Redaxo\Core\ApiFunction\Result;
use Redaxo\Core\Core;
use Redaxo\Core\Exception\UserMessageException;
use Redaxo\Core\Http\Request;
use Redaxo\Core\Translation\I18n;

/**
 * @internal
 */
class rex_api_install_package_update extends ApiFunction
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

        $installer = new rex_install_package_update();

        try {
            $message = $installer->run($addonkey, $fileId);
        } catch (UserMessageException $exception) {
            throw new ApiFunctionException($exception->getMessage());
        }

        if ($message) {
            $message = I18n::msg('install_warning_addon_not_updated', $addonkey) . '<br />' . $message;
            $success = false;
        } else {
            $message = I18n::msg('install_info_addon_updated', $addonkey);

            $success = true;
            unset($_REQUEST['addonkey']);
        }
        return new Result($success, $message);
    }
}
