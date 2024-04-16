<?php

use Redaxo\Core\ApiFunction\ApiFunction;
use Redaxo\Core\ApiFunction\ApiFunctionException;
use Redaxo\Core\ApiFunction\ApiFunctionResult;
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
            throw new ApiFunctionException('Package management is not available in live mode!');
        }
        if (!Core::getUser()?->isAdmin()) {
            throw new ApiFunctionException('You do not have the permission!');
        }
        $addonkey = rex_request('addonkey', 'string');
        try {
            rex_install_webservice::delete(rex_install_packages::getPath('?package=' . urlencode($addonkey) . '&file_id=' . rex_request('file', 'int', 0)));
        } catch (rex_functional_exception $e) {
            throw new ApiFunctionException($e->getMessage());
        }

        unset($_REQUEST['file']);
        rex_install_packages::deleteCache();
        return new ApiFunctionResult(true, I18n::msg('install_info_addon_deleted', $addonkey));
    }

    protected function requiresCsrfProtection()
    {
        return true;
    }
}
