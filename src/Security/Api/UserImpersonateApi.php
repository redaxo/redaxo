<?php

namespace Redaxo\Core\Security\Api;

use Redaxo\Core\Api\ApiException;
use Redaxo\Core\Api\ApiFunction;
use Redaxo\Core\Core;
use Redaxo\Core\Filesystem\Url;
use rex_response;

/**
 * @internal
 */
class UserImpersonateApi extends ApiFunction
{
    /**
     * @return never
     */
    public function execute()
    {
        $impersonate = rex_get('_impersonate');

        if ('_depersonate' === $impersonate) {
            Core::getProperty('login')->depersonate();

            rex_response::sendRedirect(Url::backendPage('users/users'));

            exit;
        }

        $user = Core::requireUser();
        if (!$user->isAdmin()) {
            throw new ApiException(sprintf('Current user ("%s") must be admin to impersonate another user.', $user->getLogin()));
        }

        Core::getProperty('login')->impersonate((int) $impersonate);

        rex_response::sendRedirect(Url::backendController());

        exit;
    }

    protected function requiresCsrfProtection()
    {
        return true;
    }
}
