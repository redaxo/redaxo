<?php

use Redaxo\Core\Addon\Addon;
use Redaxo\Core\ApiFunction\ApiFunction;
use Redaxo\Core\ApiFunction\Exception\ApiFunctionException;
use Redaxo\Core\ApiFunction\Result;
use Redaxo\Core\Core;
use Redaxo\Core\Filesystem\File;
use Redaxo\Core\Filesystem\Path;
use Redaxo\Core\Filesystem\Url;
use Redaxo\Core\Translation\I18n;

/**
 * @internal
 */
class rex_api_install_package_upload extends ApiFunction
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
        $file['version'] = $upload['upload_file'] ? Addon::get($addonkey)->getVersion() : $upload['oldversion'];
        $file['redaxo_versions'] = ['5.x'];
        $file['description'] = $upload['description'];
        $file['status'] = $upload['status'];
        try {
            if ($upload['upload_file']) {
                $archive = Path::addonCache('install', md5($addonkey . time()) . '.zip');
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
                /** @var list<string>|null $packageExclude */
                $packageExclude = Addon::get($addonkey)->getProperty('installer_ignore');
                if (is_array($packageExclude)) {
                    foreach ($packageExclude as $excludeItem) {
                        $exclude[] = $excludeItem;
                    }
                }
                rex_install_archive::copyDirToArchive(Path::addon($addonkey), $archive, null, $exclude);
                if ($upload['replace_assets']) {
                    rex_install_archive::copyDirToArchive(Url::addonAssets($addonkey), $archive, $addonkey . '/assets');
                }
                $file['checksum'] = md5_file($archive);
            }
            rex_install_webservice::post(rex_install_packages::getPath('?package=' . urlencode($addonkey) . '&file_id=' . rex_request('file', 'int', 0)), ['file' => $file], $archive);
        } catch (rex_functional_exception $e) {
            throw new ApiFunctionException($e->getMessage());
        } finally {
            if ($archive) {
                File::delete($archive);
            }
        }

        unset($_REQUEST['file']);
        rex_install_packages::deleteCache();
        return new Result(true, I18n::msg('install_info_addon_uploaded', $addonkey));
    }

    protected function requiresCsrfProtection()
    {
        return true;
    }
}
