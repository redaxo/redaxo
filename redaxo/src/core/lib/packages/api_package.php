<?php

class rex_api_package extends rex_api_function
{
  public function execute()
  {
    $function = rex_request('function', 'string');
    if (!in_array($function, array('install', 'uninstall', 'activate', 'deactivate', 'delete'))) {
      throw new rex_api_exception('Unknown package function "' . $function . '"!');
    }
    $packageId = rex_request('package', 'string');
    $package = rex_package::get($packageId);
    if ($function == 'uninstall' && !$package->isInstalled()
      || $function == 'activate' && $package->isActivated()
      || $function == 'deactivate' && !$package->isActivated()
      || $function == 'delete' && !rex_package::exists($packageId)) {
      return null;
    }
    if ($package instanceof rex_null_package) {
      throw new rex_api_exception('Package "' . $packageId . '" doesn\'t exists!');
    }
    $manager = rex_package_manager::factory($package);
    $success = $manager->$function();
    $message = $manager->getMessage();
    $result = new rex_api_result($success, $message);
    return $result;
  }
}
