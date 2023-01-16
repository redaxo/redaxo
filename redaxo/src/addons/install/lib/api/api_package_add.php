<?php

/**
 * @package redaxo\install
 *
 * @internal
 */
class rex_api_install_package_add extends rex_api_function
{
    public function execute()
    {
        if (!rex::getUser()?->isAdmin()) {
            throw new rex_api_exception('You do not have the permission!');
        }
        $addonkey = rex_request('addonkey', 'string');
        $fileId = rex_request('file', 'int');

        $installer = new rex_install_package_add();

        try {
            $message = $installer->run($addonkey, $fileId);
        } catch (rex_functional_exception $exception) {
            throw new rex_api_exception($exception->getMessage());
        }

        if ($message) {
            $message = rex_i18n::msg('install_warning_addon_not_downloaded', $addonkey) . '<br />' . $message;
            $success = false;
        } else {
            $package = rex_package::get($addonkey);
            $packageInstallUrl = rex_url::currentBackendPage([
                'package' => $package->getPackageId(),
                'function' => 'install',
            ] + rex_api_package::getUrlParams());

            $message = rex_i18n::msg('install_info_addon_downloaded', $addonkey)
                . ' <a href="' . rex_url::backendPage('packages', ['mark' => $addonkey]) . '">' . rex_i18n::msg('install_to_addon_page') . '</a>'
                . ' | <a href="' . $packageInstallUrl . '">' . rex_i18n::msg('install_to_addon_page_install') . '</a>';

            $success = true;
            unset($_REQUEST['addonkey']);
        }
        return new rex_api_result($success, $message);
    }

    protected function requiresCsrfProtection()
    {
        return true;
    }
}
