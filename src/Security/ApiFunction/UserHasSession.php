<?php

namespace Redaxo\Core\Security\ApiFunction;

use Redaxo\Core\ApiFunction\ApiFunction;
use Redaxo\Core\ApiFunction\Exception\ApiFunctionException;
use Redaxo\Core\Core;
use Redaxo\Core\Http\Request;
use rex_response;

/**
 * @internal
 */
class UserHasSession extends ApiFunction
{
    /**
     * @return never
     */
    public function execute()
    {
        if (!Request::isHttps()) {
            throw new ApiFunctionException('https is required');
        }

        $user = Core::getUser();
        if (!$user) {
            rex_response::sendJson(false);
            exit;
        }

        $perm = rex_get('perm');
        if ($perm) {
            rex_response::sendJson($user->hasPerm($perm));
            exit;
        }

        rex_response::sendJson(true);
        exit;
    }

    protected function requiresCsrfProtection()
    {
        // this action supports to be callable by 3rd party apps, which can't know our valid csrf token
        return false;
    }
}
