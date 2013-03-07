<?php

/**
 * @package redaxo\install
 */
class rex_api_install_core_update extends rex_api_function
{
    public static function getVersions()
    {
        return rex_install_webservice::getJson('core');
    }

    public function execute()
    {
        if (!rex::getUser()->isAdmin()) {
            throw new rex_api_exception('You do not have the permission!');
        }
        $addon = rex_addon::get('install');
        $versions = self::getVersions();
        $versionId = rex_request('version_id', 'int');
        if (!isset($versions[$versionId])) {
            return null;
        }
        $version = $versions[$versionId];
        if (!rex_string::versionCompare($version['version'], rex::getVersion(), '>')) {
            throw new rex_api_exception(sprintf('Existing version of Core (%s) is newer than %s', rex::getVersion(), $version['version']));
        }
        try {
            $archivefile = rex_install_webservice::getArchive($version['path']);
        } catch (rex_functional_exception $e) {
            throw new rex_api_exception($e->getMessage());
        }
        $message = '';
        $archive = "phar://$archivefile/core";
        $temppath = rex_path::src('_new_core/');
        if ($version['checksum'] != md5_file($archivefile)) {
            $message = $addon->i18n('warning_zip_wrong_checksum');
        } elseif (!file_exists($archive)) {
            $message = $addon->i18n('warning_zip_wrong_format');
        } else {
            $messages = [];
            foreach (rex_package::getAvailablePackages() as $package) {
                $manager = rex_package_manager::factory($package);
                if (!$manager->checkRedaxoRequirement($version['version'])) {
                    $messages[] = $package->getPackageId() . ': ' . $manager->getMessage();
                }
            }
            if (!empty($messages)) {
                $message = implode('<br />', $messages);
            }
        }
        if (!$message && !rex_dir::copy($archive, $temppath)) {
            $message = $addon->i18n('warning_core_zip_not_extracted');
        }
        if (!$message && file_exists($temppath . 'update.php')) {
            try {
                include $temppath . 'update.php';
            } catch (rex_functional_exception $e) {
                $message = $e->getMessage();
            } catch (rex_sql_exception $e) {
                $message = 'SQL error: ' . $e->getMessage();
            }
        }
        rex_file::delete($archivefile);
        if (!$message) {
            $path = rex_path::core();
            if ($addon->getConfig('backups')) {
                rex_dir::create($addon->getDataPath());
                $archive = $addon->getDataPath(strtolower(preg_replace('/[^a-z0-9-_.]/i', '_', rex::getVersion())) . '.zip');
                rex_install_helper::copyDirToArchive($path, $archive);
            }
            rex_dir::delete($path);
            rename($temppath, $path);
            if (is_dir(rex_path::core('assets'))) {
                rex_dir::copy(rex_path::core('assets'), rex_path::assets());
            }
        }
        rex_dir::delete($temppath);
        if ($message) {
            $message = $addon->i18n('warning_core_not_updated') . '<br />' . $message;
            $success = false;
        } else {
            $message = $addon->i18n('info_core_updated');
            $success = true;
            rex_install_webservice::deleteCache('core');
        }
        return new rex_api_result($success, $message);
    }


}
