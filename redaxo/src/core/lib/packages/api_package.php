<?php

class rex_api_package extends rex_api_function
{
  public function execute()
  {
    $packageId = rex_request('package', 'string');
    $package = rex_package::get($packageId);
    if($package instanceof rex_null_package)
    {
      throw new rex_api_exception('Package "'.$packageId.'" doesn\'t exists!');
    }
    $function = rex_request('function', 'string');
    if(!in_array($function, array('install', 'uninstall', 'activate', 'deactivate', 'delete')))
    {
      throw new rex_api_exception('Unknown package function "'.$function.'"!');
    }
    $manager = rex_package_manager::factory($package);
    $success = $manager->$function();
    $message = $manager->getMessage();
    $result = new rex_api_result($success, $message);
    return $result;
  }
}
