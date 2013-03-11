<?php

use Psr\Log\AbstractLogger;
use Psr\Log\LogLevel;

/**
 * Simple Logger class
 *
 * @author staabm
 * @package redaxo\core
 */
class rex_logger extends AbstractLogger
{
    use rex_factory;

    private static $handle;

    /**
     * Shorthand: Logs the given Exception
     *
     * @param Exception $exception The Exception to log
     */
    public static function logException(Exception $exception)
    {
        if ($exception instanceof ErrorException) {
            self::logError($exception->getSeverity(), $exception->getMessage(), $exception->getFile(), $exception->getLine());
        } else {
                    $logger = self::factory();
            $logger->error('<div><b>' . get_class($exception) . '</b>: ' . $exception->getMessage() . ' in <b>' . $exception->getFile() . '</b> on line <b>' . $exception->getLine() . '</b></div>');
        }
    }

    /**
     * Shorthand: Logs a error message
     *
     * @param integer $errno   The error code to log
     * @param string  $errstr  The error message
     * @param string  $errfile The file in which the error occured
     * @param integer $errline The line of the file in which the error occured
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
        $logger->log(self::getLogLevel($errno), '<div><b>' . rex_error_handler::getErrorType($errno) . "</b>[$errno]: $errstr in <b>$errfile</b> on line <b>$errline</b></div>");
    }

    /**
     * Logs with an arbitrary level.
     *
     * @param mixed  $level
     * @param string $message
     * @param array  $context
     *
     * @throws rex_exception
     */
    public function log($level, $message, array $context = [])
    {
        if (static::hasFactoryClass()) {
            static::callFactoryClass(__FUNCTION__, func_get_args());
            return;
        }

        if (!is_string($message)) {
            throw new InvalidArgumentException('Expecting $message to be string, but ' . gettype($message) . ' given!');
        }

        self::open();
        if (is_resource(self::$handle)) {
            // build a replacement array with braces around the context keys
            $replace = [];
            foreach ($context as $key => $val) {
                $replace['{' . $key . '}'] = $val;
            }

            // interpolate replacement values into the message and return
            $message = strtr($message, $replace);

            fwrite(self::$handle, '<div>' . date('r') . '</div>' . $message . "\n");

            // forward the error into phps' error log
            error_log($message, 0);
        }
    }

    /**
     * Prepares the logifle for later use
     */
    public static function open()
    {
        // check if already opened
        if (!self::$handle) {
            $file = rex_path::cache('system.log');
            self::$handle = fopen($file, 'ab');

            if (!self::$handle) {
                echo 'Error while creating logfile ' . $file;
                exit();
            }
        }
    }

    /**
     * Closes the logfile. The logfile is not be able to log further message after beeing closed.
     *
     * You dont need to close the logfile manually when it was registered during the request.
     */
    public static function close()
    {
        if (is_resource(self::$handle)) {
            fclose(self::$handle);
        }
    }

    /**
     * Map php error codes to PSR3 error levels
     *
     * @param int $errno a php error code, e.g. E_ERROR
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

    public static function factory()
    {
        $class = self::getFactoryClass();
        return new $class();
    }
}
