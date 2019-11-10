<?php

use Psr\Log\LogLevel;

/**
 * Class to monitor extension points.
 *
 * @author staabm
 *
 * @package redaxo\debug
 */
class rex_logger_debug extends rex_logger
{
    /**
     * Logs with an arbitrary level.
     *
     * @param mixed  $level
     * @param string $message
     * @param string $file
     * @param int    $line
     *
     * @throws InvalidArgumentException
     */
    public function log($level, $message, array $context = [], $file = null, $line = null)
    {
        $levelType = is_int($level) ? self::getLogLevel($level) : $level;

        if (in_array($levelType, [LogLevel::NOTICE, LogLevel::INFO])) {
            ChromePhp::log($message);
        } elseif (in_array($levelType, [LogLevel::WARNING])) {
            ChromePhp::warn($message);
        } else {
            ChromePhp::error($message);
        }

        parent::log($level, $message, $context, $file, $line);
    }
}
