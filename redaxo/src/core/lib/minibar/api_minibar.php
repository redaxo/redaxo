<?php

/**
 * @package redaxo\core
 *
 */
class rex_api_minibar extends rex_api_function
{
    public function execute()
    {
        rex_minibar::setVisibility(rex_get('visibility', 'bool', false));
        rex_response::sendRedirect(rex_url::currentBackendPage([], false));
    }

    protected function requiresCsrfProtection()
    {
        return true;
    }
}
