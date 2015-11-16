<?php

/**
 * @package redaxo\core
 */
class rex_api_package extends rex_api_function
{
    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $function = rex_request('function', 'string');
        if (!in_array($function, ['install', 'uninstall', 'activate', 'deactivate', 'delete'])) {
            throw new rex_api_exception('Unknown package function "' . $function . '"!');
        }
        $packageId = rex_request('package', 'string');
        $package = rex_package::get($packageId);
        if ($function == 'uninstall' && !$package->isInstalled()
            || $function == 'activate' && $package->isAvailable()
            || $function == 'deactivate' && !$package->isAvailable()
            || $function == 'delete' && !rex_package::exists($packageId)
        ) {
            throw new rex_api_exception('Illegal operation "' . $function . '" for package "' . $packageId . '"');
        }

        if ($package instanceof rex_null_package) {
            throw new rex_api_exception('Package "' . $packageId . '" doesn\'t exists!');
        }
        $reinstall = 'install' === $function && $package->isInstalled();
        $manager = rex_package_manager::factory($package);
        $success = $manager->$function();
        $message = $manager->getMessage();
        $result = new rex_api_result($success, $message);
        if ($success && !$reinstall) {
            $result->setRequiresReboot(true);
        }
        return $result;
    }
}
