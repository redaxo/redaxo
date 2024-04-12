<?php

use Redaxo\Core\Api\ApiFunction;
use Redaxo\Core\Api\ApiResult;
use Redaxo\Core\Core;

/**
 * @internal
 */
class rex_api_debug extends ApiFunction
{
    public function execute()
    {
        if (!Core::isDebugMode() || !Core::getUser()?->isAdmin()) {
            return new ApiResult(false);
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
