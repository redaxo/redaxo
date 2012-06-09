<?php

abstract class rex_api_install_packages_download extends rex_api_function
{
  protected
    $addonkey,
    $fileId,
    $file,
    $archive;

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
    $this->archive = "phar://$archivefile/" . $this->addonkey;
    if ($this->file['checksum'] != md5_file($archivefile)) {
      $message = rex_i18n::msg('install_packages_warning_zip_wrong_checksum');
    } elseif (!file_exists($this->archive)) {
      $message = rex_i18n::msg('install_packages_warning_zip_wrong_format');
    } elseif (is_string($msg = $this->doAction())) {
      $message = $msg;
    }
    rex_file::delete($archivefile);
    if ($message) {
      $message = rex_i18n::msg('install_packages_warning_not_' . static::VERB, $this->addonkey) . '<br />' . $message;
      $success = false;
    } else {
      $message = rex_i18n::msg('install_packages_info_addon_' . static::VERB, $this->addonkey)
               . (static::SHOW_LINK ? ' <a href="index.php?page=addon">' . rex_i18n::msg('install_packages_to_addon_page') . '</a>' : '');
      $success = true;
      unset($_REQUEST['addonkey']);
    }
    return new rex_api_result($success, $message);
  }

  protected function extractArchiveTo($dir)
  {
    if (!rex_dir::copy($this->archive, $dir)) {
      rex_dir::delete($dir);
      return rex_i18n::msg('install_packages_warning_zip_not_extracted');
    }
    return true;
  }

  abstract protected function checkPreConditions();

  abstract protected function doAction();
}
