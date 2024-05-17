<?php

namespace Redaxo\Core\Security\ApiFunction;

use Redaxo\Core\ApiFunction\ApiFunction;
use Redaxo\Core\ApiFunction\Exception\ApiFunctionException;
use Redaxo\Core\Core;
use Redaxo\Core\Filesystem\Url;
use Redaxo\Core\Http\Request;
use Redaxo\Core\Http\Response;

/**
 * @internal
 */
class UserImpersonate extends ApiFunction
{
    /**
     * @return never
     */
    public function execute()
    {
        $impersonate = Request::get('_impersonate');

        if ('_depersonate' === $impersonate) {
            Core::getProperty('login')->depersonate();

            Response::sendRedirect(Url::backendPage('users/users'));

            exit;
        }

        $user = Core::requireUser();
        if (!$user->isAdmin()) {
            throw new ApiFunctionException(sprintf('Current user ("%s") must be admin to impersonate another user.', $user->getLogin()));
        }

        Core::getProperty('login')->impersonate((int) $impersonate);

        Response::sendRedirect(Url::backendController());

        exit;
    }

    protected function requiresCsrfProtection()
    {
        return true;
    }
}
