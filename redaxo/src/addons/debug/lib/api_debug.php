<?php

/**
 * @package redaxo\debug
 *
 * @internal
 */
class rex_api_debug extends rex_api_function {

    protected $published = true;
    public function execute()
    {
        $debug = rex_debug::getHelper();

        rex_response::sendJson($debug->getMetadata());
        return new rex_api_result(true);
    }

    protected function requiresCsrfProtection()
    {
        return false;
    }

    public static function getUrlParams()
    {
        return [
            self::REQ_CALL_PARAM => 'debug',
            'request' => ''
        ];
    }
}