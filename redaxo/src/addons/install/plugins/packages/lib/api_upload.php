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
    $upload = rex_request('upload', 'array');
    $data = array();
    $archive = null;
    $data['version'] = rex_addon::get($addonkey)->getVersion();
    $data['redaxo_versions'] = array('5.0.x');
    $data['description'] = $upload['description'];
    $data['status'] = (integer) isset($upload['status']) && $upload['status'];
    try
    {
      if(isset($upload['upload_file']) && $upload['upload_file'])
      {
        $archive = rex_path::cache('install/'. md5($addonkey . time()) .'.zip');
        rex_install_helper::copyDirToArchive(rex_path::addon($addonkey), $archive);
        $data['checksum'] = md5_file($archive);
      }
      rex_install_webservice::post('packages/?package='.$addonkey.'&file_id='.rex_request('file', 'int', 0), $data, $archive);
    }
    catch(rex_functional_exception $e)
    {
      throw new rex_api_exception($e->getMessage());
    }
    if($archive)
      rex_file::delete($archive);
    unset($_REQUEST['addonkey']);
    unset($_REQUEST['file']);
    return new rex_api_result(true, rex_i18n::msg('install_packages_info_addon_uploaded', $addonkey));
  }
}