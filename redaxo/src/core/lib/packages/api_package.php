<?php

use Redaxo\Core\Core;
use Redaxo\Core\Util\Type;

/**
 * @internal
 */
final class rex_api_package extends rex_api_function
{
    #[Override]
    public function execute(): rex_api_result
    {
        if (Core::isLiveMode()) {
            throw new rex_api_exception('Package management is not available in live mode!');
        }

        $function = rex_request('function', 'string');
        if (!in_array($function, ['install', 'uninstall', 'activate', 'deactivate', 'delete'])) {
            throw new rex_api_exception('Unknown package function "' . $function . '"!');
        }
        $packageId = rex_request('package', 'string');
        $package = rex_addon::get($packageId);
        if ('uninstall' == $function && !$package->isInstalled()
            || 'activate' == $function && $package->isAvailable()
            || 'deactivate' == $function && !$package->isAvailable()
            || 'delete' == $function && !rex_addon::exists($packageId)
        ) {
            return new rex_api_result(true);
        }

        if (!$package instanceof rex_addon) {
            throw new rex_api_exception('Package "' . $packageId . '" doesn\'t exists!');
        }
        $reinstall = 'install' === $function && $package->isInstalled();
        $manager = rex_addon_manager::factory($package);
        $success = Type::bool($manager->$function());
        $message = $manager->getMessage();
        $result = new rex_api_result($success, $message);
        if ($success && !$reinstall) {
            $result->setRequiresReboot(true);
        }
        return $result;
    }

    #[Override]
    protected function requiresCsrfProtection(): true
    {
        return true;
    }
}
