<?php

/**
 * @package redaxo\core\login
 *
 * @internal
 */
class rex_api_has_user_session extends rex_api_function
{
    public function execute()
    {
        if (!rex_request::isHttps()) {
            throw new rex_api_exception(sprintf('https is required'));
        }

        $user = rex::getUser();
        if (!$user) {
            rex_response::sendContent(json_encode(false), 'application/json');
            exit();
        }

        $perm = rex_get('perm');
        if ($perm) {
            rex_response::sendContent(json_encode($user->hasPerm($perm)), 'application/json');
            exit();
        }

        rex_response::sendContent(json_encode(true), 'application/json');
        exit();
    }

    protected function requiresCsrfProtection()
    {
        // this action supports to be callable by 3rd party apps, which can't know our valid csrf token
        return false;
    }
}
