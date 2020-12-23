<?php

/**
 * @package redaxo\debug
 *
 * @internal
 */
abstract class rex_api_function_debug extends rex_api_function
{
    public static function handleCall()
    {
        $apiFunc = self::factory();

        if (null != $apiFunc) {
            rex_debug_clockwork::getInstance()->log('debug', 'called api function "' . get_class(self::factory()) . '"');
        }
        return parent::handleCall();
    }
}
