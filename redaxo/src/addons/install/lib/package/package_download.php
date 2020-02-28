<?php

/**
 * @package redaxo\install
 *
 * @internal
 */
abstract class rex_install_package_download
{
    protected $addonkey;
    protected $fileId;
    protected $file;
    protected $archive;

    public function run(string $addonkey, int $fileId)
    {
        $this->addonkey = $addonkey;
        $this->fileId = $fileId;

        $packages = $this->getPackages();
        if (!isset($packages[$this->addonkey]['files'][$this->fileId])) {
            throw new rex_functional_exception('The requested addon version can not be loaded, maybe it is already installed.');
        }
        $this->file = $packages[$this->addonkey]['files'][$this->fileId];
        $this->checkPreConditions();

        $archivefile = rex_install_webservice::getArchive($this->file['path']);

        $message = '';
        $this->archive = $archivefile;

        try {
            if ($this->file['checksum'] != md5_file($archivefile)) {
                $message = rex_i18n::msg('install_warning_zip_wrong_checksum');
            } elseif (!$this->isCorrectFormat($archivefile)) {
                $message = rex_i18n::msg('install_warning_zip_wrong_format');
            } elseif (is_string($msg = $this->doAction())) {
                $message = $msg;
            }
        } finally {
            rex_file::delete($archivefile);
        }

        return $message;
    }

    /**
     * @return string|true
     */
    protected function extractArchiveTo($dir)
    {
        if (!rex_install_archive::extract($this->archive, $dir, $this->addonkey)) {
            rex_dir::delete($dir);
            return rex_i18n::msg('install_warning_addon_zip_not_extracted');
        }
        return true;
    }

    abstract protected function getPackages();

    abstract protected function checkPreConditions();

    abstract protected function doAction();

    /**
     * @return bool
     */
    private function isCorrectFormat($file)
    {
        if (class_exists('ZipArchive')) {
            $success = false;
            $zip = new ZipArchive();
            if (true === $zip->open($file)) {
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
