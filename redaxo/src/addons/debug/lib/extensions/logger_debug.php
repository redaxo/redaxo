<?php

use Clockwork\Helpers\StackTrace;
use Redaxo\Core\Util\Logger;

/**
 * @internal
 */
class rex_logger_debug extends Logger
{
    public function log($level, $message, array $context = [], $file = null, $line = null, ?string $url = null): void
    {
        $levelType = is_int($level) ? self::getLogLevel($level) : $level;

        $trace = StackTrace::from(rex_debug::getTrace()['trace']);
        rex_debug_clockwork::getInstance()->log($levelType, $message, ['trace' => $trace]);

        parent::log($level, $message, $context, $file, $line);
    }
}
