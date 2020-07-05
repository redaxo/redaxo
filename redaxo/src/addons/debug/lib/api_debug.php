<?php

/**
 * @package redaxo\debug
 *
 * @internal
 */
class rex_api_debug extends rex_api_function
{
    public function execute()
    {
        if (!rex::isDebugMode() || !(rex::getUser() && rex::getUser()->isAdmin())) {
            return new rex_api_result(false);
        }

        $debug = rex_debug_clockwork::getHelper();

        rex_response::sendJson($debug->getMetadata());
        exit;
    }

    protected function requiresCsrfProtection()
    {
        return false;
    }

    public static function getUrlParams()
    {
        return [
            self::REQ_CALL_PARAM => 'debug',
            'request' => '',
        ];
    }
}
