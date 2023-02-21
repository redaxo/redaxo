<?php

/**
 * @package redaxo\debug
 *
 * @internal
 */
class rex_logger_debug extends rex_logger
{
    public function log($level, $message, array $context = [], $file = null, $line = null, ?string $url = null)
    {
        $levelType = is_int($level) ? self::getLogLevel($level) : $level;

        $trace = \Clockwork\Helpers\StackTrace::from(rex_debug::getTrace()['trace']);
        rex_debug_clockwork::getInstance()->log($levelType, $message, ['trace' => $trace]);

        parent::log($level, $message, $context, $file, $line);
    }
}
