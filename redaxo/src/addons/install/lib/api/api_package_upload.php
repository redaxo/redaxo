<?php

/**
 * @package redaxo\install
 *
 * @internal
 */
class rex_api_install_package_upload extends rex_api_function
{
    public function execute()
    {
        if (!rex::getUser()?->isAdmin()) {
            throw new rex_api_exception('You do not have the permission!');
        }
        $addonkey = rex_request('addonkey', 'string');
        $upload = rex_request('upload', [
            ['upload_file', 'bool'],
            ['oldversion', 'string'],
            ['description', 'string'],
            ['status', 'int'],
            ['replace_assets', 'bool'],
            ['ignore_tests', 'bool'],
        ]);
        $file = [];
        $archive = null;
        $file['version'] = $upload['upload_file'] ? rex_addon::get($addonkey)->getVersion() : $upload['oldversion'];
        $file['redaxo_versions'] = ['5.x'];
        $file['description'] = $upload['description'];
        $file['status'] = $upload['status'];
        try {
            if ($upload['upload_file']) {
                $archive = rex_path::addonCache('install', md5($addonkey . time()) . '.zip');
                $exclude = [
                    '.gitattributes',
                    '.github',
                    '.gitignore',
                    '.idea',
                    '.vscode',
                ];
                if ($upload['replace_assets']) {
                    $exclude[] = 'assets';
                }
                if ($upload['ignore_tests']) {
                    $exclude[] = 'tests';
                }
                /** @var string[]|null $packageExclude */
                $packageExclude = rex_addon::get($addonkey)->getProperty('installer_ignore');
                if (is_array($packageExclude)) {
                    foreach ($packageExclude as $excludeItem) {
                        $exclude[] = $excludeItem;
                    }
                }
                rex_install_archive::copyDirToArchive(rex_path::addon($addonkey), $archive, null, $exclude);
                if ($upload['replace_assets']) {
                    rex_install_archive::copyDirToArchive(rex_url::addonAssets($addonkey), $archive, $addonkey . '/assets');
                }
                $file['checksum'] = md5_file($archive);
            }
            rex_install_webservice::post(rex_install_packages::getPath('?package=' . urlencode($addonkey) . '&file_id=' . rex_request('file', 'int', 0)), ['file' => $file], $archive);
        } catch (rex_functional_exception $e) {
            throw new rex_api_exception($e->getMessage());
        } finally {
            if ($archive) {
                rex_file::delete($archive);
            }
        }

        unset($_REQUEST['file']);
        rex_install_packages::deleteCache();
        return new rex_api_result(true, rex_i18n::msg('install_info_addon_uploaded', $addonkey));
    }

    protected function requiresCsrfProtection()
    {
        return true;
    }
}
