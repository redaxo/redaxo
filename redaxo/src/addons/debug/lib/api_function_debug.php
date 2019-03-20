<?php

/**
 * @package redaxo\debug
 */
abstract class rex_api_function_debug extends rex_api_function
{
    public static function handleCall()
    {
        $apiFunc = self::factory();

        if (null != $apiFunc) {
            ChromePhp::group(self::class);
            ChromePhp::log('called api function "' . get_class(self::factory()) . '"');
            ChromePhp::groupEnd();
        }
        return parent::handleCall();
    }
}
