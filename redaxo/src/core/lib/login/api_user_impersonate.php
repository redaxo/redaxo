<?php

use Redaxo\Core\Core;

/**
 * @internal
 */
class rex_api_user_impersonate extends rex_api_function
{
    /**
     * @return never
     */
    public function execute()
    {
        $impersonate = rex_get('_impersonate');

        if ('_depersonate' === $impersonate) {
            Core::getProperty('login')->depersonate();

            rex_response::sendRedirect(rex_url::backendPage('users/users'));

            exit;
        }

        $user = Core::requireUser();
        if (!$user->isAdmin()) {
            throw new rex_api_exception(sprintf('Current user ("%s") must be admin to impersonate another user.', $user->getLogin()));
        }

        Core::getProperty('login')->impersonate((int) $impersonate);

        rex_response::sendRedirect(rex_url::backendController());

        exit;
    }

    protected function requiresCsrfProtection()
    {
        return true;
    }
}
