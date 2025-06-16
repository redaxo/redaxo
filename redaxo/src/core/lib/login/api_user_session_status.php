<?php

/**
 * @package redaxo\core\login
 *
 * @internal
 */
class rex_api_user_session_status extends rex_api_function
{
    /**
     * @return never
     */
    public function execute()
    {
        $user = rex::getUser();
        if (!$user) {
            rex_response::sendJson(false);
            exit;
        }

        $login = rex::getProperty('login');

        $rest_overall_time = rex::getProperty('session_max_overall_duration', 0) + $login->getSessionVar(rex_backend_login::SESSION_START_TIME) - $login->getSessionVar(rex_backend_login::SESSION_LAST_ACTIVITY);
        rex_response::sendJson([
            'rest_overall_time' => $rest_overall_time,
        ]);

        exit;
    }

    protected function requiresCsrfProtection()
    {
        // this action supports to be callable by 3rd party apps, which can't know our valid csrf token
        return false;
    }
}
