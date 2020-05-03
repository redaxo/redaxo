<?php

use Psr\Log\AbstractLogger;
use Psr\Log\LogLevel;

/**
 * Simple Logger class.
 *
 * @author staabm
 *
 * @package redaxo\core
 */
class rex_logger extends AbstractLogger
{
    use rex_factory_trait;

    /**
     * @var rex_log_file|null
     */
    private static $file;

    /**
     * Returns the path to the system.log file.
     *
     * @return string
     */
    public static function getPath()
    {
        return rex_path::log('system.log');
    }

    /**
     * Shorthand: Logs the given Exception.
     *
     * @param Throwable|Exception $exception The Exception to log
     */
    public static function logException($exception)
    {
        if ($exception instanceof ErrorException) {
            self::logError($exception->getSeverity(), $exception->getMessage(), $exception->getFile(), $exception->getLine());
        } else {
            $logger = self::factory();
            $logger->log(get_class($exception), $exception->getMessage(), [], $exception->getFile(), $exception->getLine());
        }
    }

    /**
     * Shorthand: Logs a error message.
     *
     * @param int    $errno   The error code to log, e.g. E_WARNING
     * @param string $errstr  The error message
     * @param string $errfile The file in which the error occured
     * @param int    $errline The line of the file in which the error occured
     *
     * @throws InvalidArgumentException
     */
    public static function logError($errno, $errstr, $errfile, $errline)
    {
        if (!is_int($errno)) {
            throw new InvalidArgumentException('Expecting $errno to be integer, but ' . gettype($errno) . ' given!');
        }
        if (!is_string($errstr)) {
            throw new InvalidArgumentException('Expecting $errstr to be string, but ' . gettype($errstr) . ' given!');
        }
        if (!is_string($errfile)) {
            throw new InvalidArgumentException('Expecting $errfile to be string, but ' . gettype($errfile) . ' given!');
        }
        if (!is_int($errline)) {
            throw new InvalidArgumentException('Expecting $errline to be integer, but ' . gettype($errline) . ' given!');
        }

        $logger = self::factory();
        $logger->log(rex_error_handler::getErrorType($errno), $errstr, [], $errfile, $errline);
    }

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
        if (static::hasFactoryClass()) {
            static::callFactoryClass(__FUNCTION__, func_get_args());
            return;
        }

        if (!is_string($message)) {
            throw new InvalidArgumentException('Expecting $message to be string, but ' . gettype($message) . ' given!');
        }

        self::open();
        // build a replacement array with braces around the context keys
        $replace = [];
        foreach ($context as $key => $val) {
            $replace['{' . $key . '}'] = $val;
        }

        // interpolate replacement values into the message and return
        $message = strtr($message, $replace);

        $logData = [$level, $message];
        if ($file && $line) {
            $logData[] = rex_path::relative($file);
            $logData[] = $line;
        }
        self::$file->add($logData);

        // forward the error into phps' error log
        error_log($message, 0);
    }

    /**
     * Prepares the logifle for later use.
     */
    public static function open()
    {
        // check if already opened
        if (!self::$file) {
            self::$file = new rex_log_file(self::getPath(), 2000000);
        }
    }

    /**
     * Closes the logfile. The logfile is not be able to log further message after beeing closed.
     *
     * You dont need to close the logfile manually when it was registered during the request.
     */
    public static function close()
    {
        self::$file = null;
    }

    /**
     * Map php error codes to PSR3 error levels.
     *
     * @param int $errno a php error code, e.g. E_ERROR
     *
     * @return string
     */
    public static function getLogLevel($errno)
    {
        switch ($errno) {
            case E_STRICT:

            case E_USER_DEPRECATED:
            case E_DEPRECATED:

            case E_USER_WARNING:
            case E_WARNING:
            case E_COMPILE_WARNING:
                return LogLevel::WARNING;

            case E_USER_NOTICE:
            case E_NOTICE:
                return LogLevel::NOTICE;

            default:
                return LogLevel::ERROR;
        }
    }

    /**
     * @return self
     */
    public static function factory()
    {
        $class = self::getFactoryClass();
        return new $class();
    }
}
