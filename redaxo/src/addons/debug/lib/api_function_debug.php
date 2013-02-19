<?php

abstract class rex_api_function_debug extends rex_api_function
{
    public static function handleCall()
    {
        $apiFunc = self::factory();

        if ($apiFunc != null) {
            $firephp = FirePHP::getInstance(true);
            $firephp->group(__CLASS__);
            $firephp->log('called api function "' . get_class(self::factory()) . '"');
            $firephp->groupEnd();
        }
        return parent::handleCall();
    }
}
