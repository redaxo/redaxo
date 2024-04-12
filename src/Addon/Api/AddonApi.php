<?php

namespace Redaxo\Core\Addon\Api;

use Override;
use Redaxo\Core\Addon\Addon;
use Redaxo\Core\Addon\AddonManager;
use Redaxo\Core\Api\ApiException;
use Redaxo\Core\Api\ApiFunction;
use Redaxo\Core\Api\ApiResult;
use Redaxo\Core\Core;
use Redaxo\Core\Util\Type;

use function in_array;

/**
 * @internal
 */
final class AddonApi extends ApiFunction
{
    #[Override]
    public function execute(): ApiResult
    {
        if (Core::isLiveMode()) {
            throw new ApiException('Package management is not available in live mode!');
        }

        $function = rex_request('function', 'string');
        if (!in_array($function, ['install', 'uninstall', 'activate', 'deactivate', 'delete'])) {
            throw new ApiException('Unknown package function "' . $function . '"!');
        }
        $packageId = rex_request('package', 'string');
        $package = Addon::get($packageId);
        if ('uninstall' == $function && !$package->isInstalled()
            || 'activate' == $function && $package->isAvailable()
            || 'deactivate' == $function && !$package->isAvailable()
            || 'delete' == $function && !Addon::exists($packageId)
        ) {
            return new ApiResult(true);
        }

        if (!$package instanceof Addon) {
            throw new ApiException('Package "' . $packageId . '" doesn\'t exists!');
        }
        $reinstall = 'install' === $function && $package->isInstalled();
        $manager = AddonManager::factory($package);
        $success = Type::bool($manager->$function());
        $message = $manager->getMessage();
        $result = new ApiResult($success, $message);
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
