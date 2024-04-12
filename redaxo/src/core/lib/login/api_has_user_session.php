<?php

use Redaxo\Core\Api\ApiException;
use Redaxo\Core\Api\ApiFunction;
use Redaxo\Core\Core;

/**
 * @internal
 */
class rex_api_has_user_session extends ApiFunction
{
    /**
     * @return never
     */
    public function execute()
    {
        if (!rex_request::isHttps()) {
            throw new ApiException('https is required');
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
