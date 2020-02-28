<?php

/**
 * @package redaxo\debug
 *
 * @internal
 */
class rex_logger_debug extends rex_logger
{
    /**
     * {@inheritdoc}
     */
    public function log($level, $message, array $context = [], $file = null, $line = null)
    {
        $levelType = is_int($level) ? self::getLogLevel($level) : $level;

        rex_debug::getInstance()->getLog()->log($levelType, $message, ['file' => $file, 'line' => $line]);

        parent::log($level, $message, $context, $file, $line);
    }
}
