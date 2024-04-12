<?php

use Redaxo\Core\Api\ApiFunction;
use Redaxo\Core\Core;
use Redaxo\Core\Translation\I18n;

/**
 * @internal
 */
class rex_api_install_package_delete extends ApiFunction
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
        try {
            rex_install_webservice::delete(rex_install_packages::getPath('?package=' . urlencode($addonkey) . '&file_id=' . rex_request('file', 'int', 0)));
        } catch (rex_functional_exception $e) {
            throw new rex_api_exception($e->getMessage());
        }

        unset($_REQUEST['file']);
        rex_install_packages::deleteCache();
        return new rex_api_result(true, I18n::msg('install_info_addon_deleted', $addonkey));
    }

    protected function requiresCsrfProtection()
    {
        return true;
    }
}
