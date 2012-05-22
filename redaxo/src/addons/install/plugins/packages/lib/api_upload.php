<?php

class rex_api_install_packages_upload extends rex_api_function
{
  public function execute()
  {
    if(!rex::getUser()->isAdmin())
    {
      throw new rex_api_exception('You do not have the permission!');
    }
    $addonkey = rex_request('addonkey', 'string');
    $upload = rex_request('upload', array(
      array('upload_file', 'bool'),
      array('oldversion', 'string'),
      array('redaxo', 'array[string]'),
      array('description', 'string'),
      array('status', 'int'),
      array('replace_assets', 'bool'),
      array('ignore_tests', 'bool')
    ));
    $file = array();
    $archive = null;
    $file['version'] = $upload['upload_file'] ? rex_addon::get($addonkey)->getVersion() : $upload['oldversion'];
    $file['redaxo_versions'] = $upload['redaxo'];
    $file['description'] = $upload['description'];
    $file['status'] = $upload['status'];
    try
    {
      if ($upload['upload_file'])
      {
        $archive = rex_path::addonCache('install', md5($addonkey . time()) .'.zip');
        $exclude = array();
        if ($upload['replace_assets'])
        {
          $exclude[] = 'assets';
        }
        if ($upload['ignore_tests'])
        {
          $exclude[] = 'tests';
        }
        rex_install_helper::copyDirToArchive(rex_path::addon($addonkey), $archive, null, $exclude);
        if ($upload['replace_assets'])
        {
          rex_install_helper::copyDirToArchive(rex_path::addonAssets($addonkey), $archive, $addonkey .'/assets');
        }
        $file['checksum'] = md5_file($archive);
      }
      rex_install_webservice::post(rex_install_packages::getPath('?package='.$addonkey.'&file_id='.rex_request('file', 'int', 0)), array('file' => $file), $archive);
    }
    catch (rex_functional_exception $e)
    {
      throw new rex_api_exception($e->getMessage());
    }
    if ($archive)
      rex_file::delete($archive);
    unset($_REQUEST['addonkey']);
    unset($_REQUEST['file']);
    rex_install_packages::deleteCache();
    return new rex_api_result(true, rex_i18n::msg('install_packages_info_addon_uploaded', $addonkey));
  }
}
