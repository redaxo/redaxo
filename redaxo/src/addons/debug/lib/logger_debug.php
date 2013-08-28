<?php

use Psr\Log\LogLevel;
/**
 * Class to monitor extension points
 *
 * @author staabm
 * @package redaxo\debug
 */
class rex_logger_debug extends rex_logger
{
    /**
     * Logs with an arbitrary level.
     *
     * @param mixed  $level
     * @param string $message
     * @param array  $context
     * @param string $file
     * @param int    $line
     * @throws InvalidArgumentException
     * @return null
     */
    public function log($level, $message, array $context = [], $file = null, $line = null)
    {
        $levelType = is_int($level) ? self::getLogLevel($level) : $level;
        $firephp = FirePHP::getInstance(true);

        if (in_array($levelType, [LogLevel::NOTICE, LogLevel::INFO])) {
            $firephp->log($message);
        } elseif (in_array($levelType, [LogLevel::WARNING])) {
            $firephp->warn($message);
        } else {
            $firephp->error($message);
        }

        parent::log($level, $message, $context, $file, $line);
    }
}
