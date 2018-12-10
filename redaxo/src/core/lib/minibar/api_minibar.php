<?php

/**
 * @package redaxo\core
 */
class rex_api_minibar extends rex_api_function
{
    protected $published = true;

    public function execute()
    {
        rex_minibar::getInstance()->setVisibility(rex_get('visibility', 'bool', false));

        if (rex::isBackend()) {
            rex_response::sendRedirect(rex_url::currentBackendPage([], false));
        }

        rex_response::sendRedirect(rex_getUrl('', '', [], '&'));
    }

    protected function requiresCsrfProtection()
    {
        return true;
    }
}
