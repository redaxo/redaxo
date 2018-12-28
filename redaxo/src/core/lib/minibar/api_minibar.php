<?php

/**
 * @package redaxo\core
 */
class rex_api_minibar extends rex_api_function
{
    protected $published = true;

    public function execute()
    {
        $visibility = rex_get('visibility', 'bool', null);
        if ($visibility !== null) {
            rex_minibar::getInstance()->setVisibility($visibility);

            if (rex::isBackend()) {
                rex_response::sendRedirect(rex_url::currentBackendPage([], false));
            }

            rex_response::sendRedirect(rex_getUrl('', '', [], '&'));
        }

        $asyncElement = rex_get('async_element', 'string');
        if ($asyncElement) {
            $minibar = rex_minibar::getInstance();
            $element = $minibar->elementByClass($asyncElement);
            if ($element) {
                $fragment = new rex_fragment([
                    'element' => $element,
                ]);

                rex_response::sendContent($fragment->parse('core/minibar/minibar_element.php'));
                exit();
            }
        }
    }

    protected function requiresCsrfProtection()
    {
        return true;
    }
}
