<?php

/**
 * @package redaxo\install
 *
 * @internal
 */
abstract class rex_api_install_package_download extends rex_api_function
{
    protected $addonkey;
    protected $fileId;
    protected $file;
    protected $archive;

    public function execute()
    {
        if (!rex::getUser()->isAdmin()) {
            throw new rex_api_exception('You do not have the permission!');
        }
        $this->addonkey = rex_request('addonkey', 'string');
        $function = static::GET_PACKAGES_FUNCTION;
        $packages = rex_install_packages::$function();
        $this->fileId = rex_request('file', 'int');
        if (!isset($packages[$this->addonkey]['files'][$this->fileId])) {
            return null;
        }
        $this->file = $packages[$this->addonkey]['files'][$this->fileId];
        $this->checkPreConditions();
        try {
            $archivefile = rex_install_webservice::getArchive($this->file['path']);
        } catch (rex_functional_exception $e) {
            throw new rex_api_exception($e->getMessage());
        }
        $message = '';
        $this->archive = $archivefile;
        if ($this->file['checksum'] != md5_file($archivefile)) {
            $message = rex_i18n::msg('install_warning_zip_wrong_checksum');
        } elseif (!$this->isCorrectFormat($archivefile)) {
            $message = rex_i18n::msg('install_warning_zip_wrong_format');
        } elseif (is_string($msg = $this->doAction())) {
            $message = $msg;
        }
        rex_file::delete($archivefile);
        if ($message) {
            $message = rex_i18n::msg('install_warning_addon_not_' . static::VERB, $this->addonkey) . '<br />' . $message;
            $success = false;
        } else {
            $message = rex_i18n::msg('install_info_addon_' . static::VERB, $this->addonkey)
                             . (static::SHOW_LINK ? ' <a href="' . rex_url::backendPage('packages') . '">' . rex_i18n::msg('install_to_addon_page') . '</a>' : '');
            $success = true;
            unset($_REQUEST['addonkey']);
        }
        return new rex_api_result($success, $message);
    }

    protected function extractArchiveTo($dir)
    {
        if (!rex_install_archive::extract($this->archive, $dir, $this->addonkey)) {
            rex_dir::delete($dir);
            return rex_i18n::msg('install_warning_addon_zip_not_extracted');
        }
        return true;
    }

    abstract protected function checkPreConditions();

    abstract protected function doAction();

    private function isCorrectFormat($file)
    {
        if (class_exists('ZipArchive')) {
            $success = false;
            $zip = new ZipArchive();
            if ($zip->open($file) === true) {
                for ($i = 0; $i < $zip->numFiles; ++$i) {
                    $filename = $zip->getNameIndex($i);
                    if (substr($filename, 0, strlen($this->addonkey.'/')) != $this->addonkey.'/') {
                        $zip->deleteIndex($i);
                    } else {
                        $success = true;
                    }
                }
                $zip->close();
            }
            return $success;
        }

        return file_exists("phar://$file/" . $this->addonkey);
    }
}
