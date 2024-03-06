<?php

use Redaxo\Core\Core;
use Redaxo\Core\Translation\I18n;

/**
 * @internal
 */
class rex_api_install_package_update extends rex_api_function
{
    public function execute()
    {
        if (Core::isLiveMode()) {
            throw new rex_api_exception('Package management is not available in live mode!');
        }
        if (!Core::getUser()?->isAdmin()) {
            throw new rex_api_exception('You do not have the permission!');
        }
        $addonkey = rex_request('addonkey', 'string');
        $fileId = rex_request('file', 'int');

        $installer = new rex_install_package_update();

        try {
            $message = $installer->run($addonkey, $fileId);
        } catch (rex_functional_exception $exception) {
            throw new rex_api_exception($exception->getMessage());
        }

        if ($message) {
            $message = I18n::msg('install_warning_addon_not_updated', $addonkey) . '<br />' . $message;
            $success = false;
        } else {
            $message = I18n::msg('install_info_addon_updated', $addonkey);

            $success = true;
            unset($_REQUEST['addonkey']);
        }
        return new rex_api_result($success, $message);
    }
}
