<?php

/**
 * @package redaxo\core\login
 *
 * @internal
 */
class rex_api_user_impersonate extends rex_api_function
{
    /**
     * @psalm-return never-return
     */
    public function execute()
    {
        $impersonate = rex_get('_impersonate');

        if ('_depersonate' === $impersonate) {
            rex::getProperty('login')->depersonate();

            rex_response::sendRedirect(rex_url::backendPage('users/users', [], false));

            exit;
        }

        $user = rex::requireUser();
        if (!$user->isAdmin()) {
            throw new rex_api_exception(sprintf('Current user ("%s") must be admin to impersonate another user.', $user->getLogin()));
        }

        rex::getProperty('login')->impersonate((int) $impersonate);

        rex_response::sendRedirect(rex_url::backendController([], false));

        exit;
    }

    protected function requiresCsrfProtection()
    {
        return true;
    }
}
