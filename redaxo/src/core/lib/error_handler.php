<?php

/**
 * @package redaxo\core
 */
abstract class rex_error_handler
{
    private static $registered;

    /**
     * Registers the class as php-error/exception handler.
     */
    public static function register()
    {
        if (self::$registered) {
            return;
        }

        self::$registered = true;

        set_error_handler([__CLASS__, 'handleError']);
        set_exception_handler([__CLASS__, 'handleException']);
        register_shutdown_function([__CLASS__, 'shutdown']);
    }

    /**
     * Unregisters the logger instance as php-error/exception handler.
     */
    public static function unregister()
    {
        if (!self::$registered) {
            return;
        }

        self::$registered = false;

        restore_error_handler();
        restore_exception_handler();
        // unregister of shutdown function is not possible
    }

    /**
     * Handles the given Exception.
     *
     * @param Throwable|Exception $exception The Exception to handle
     */
    public static function handleException($exception)
    {
        rex_logger::logException($exception);

        while (ob_get_level()) {
            ob_end_clean();
        }

        $status = rex_response::HTTP_INTERNAL_ERROR;
        if ($exception instanceof rex_http_exception && $exception->getHttpCode()) {
            $status = $exception->getHttpCode();
        }
        rex_response::setStatus($status);

        if (rex::isSetup() || rex::isDebugMode() || ($user = rex_backend_login::createUser()) && $user->isAdmin()) {
            $whoops = new \Whoops\Run;
            $whoops->writeToOutput(false);
            $whoops->allowQuit(false);
            $handler = new \Whoops\Handler\PrettyPageHandler();
            $whoops->pushHandler($handler);

            $errPage = $whoops->handleException($exception);
            if (!rex::isSetup() && rex::isBackend() && !rex::isSafeMode()) {
                $errPage = str_replace(
                    '</body>',
                    '<a 
                       href="' . rex_url::backendPage('packages', ['safemode' => 1]) . '" 
                       style="position:absolute;top:20px;right:40px;">activate safe mode</a></body>',
                    $errPage
                );
            }

            rex_response::sendContent($errPage, $handler->contentType());
            exit;
        }

        // TODO small error page, without debug infos
        $buf = 'Oooops, an internal error occured!';
        rex_response::sendContent($buf);
        exit;

    }

    /**
     * Handles a error message.
     *
     * @param int    $errno   The error code to handle
     * @param string $errstr  The error message
     * @param string $errfile The file in which the error occured
     * @param int    $errline The line of the file in which the error occured
     *
     * @throws ErrorException
     */
    public static function handleError($errno, $errstr, $errfile, $errline)
    {
        if (in_array($errno, [E_USER_ERROR, E_ERROR, E_COMPILE_ERROR, E_RECOVERABLE_ERROR, E_PARSE])) {
            throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
        } elseif ((error_reporting() & $errno) == $errno) {
            if (ini_get('display_errors') && (rex::isSetup() || rex::isDebugMode() || ($user = rex_backend_login::createUser()) && $user->isAdmin())) {
                echo '<div><b>' . self::getErrorType($errno) . "</b>: $errstr in <b>$errfile</b> on line <b>$errline</b></div>";
            }
            rex_logger::logError($errno, $errstr, $errfile, $errline);
        }
    }

    /**
     * Shutdown-handler which is called at the very end of the request.
     */
    public static function shutdown()
    {
        // catch fatal/parse errors
        if (self::$registered) {
            $error = error_get_last();
            if (is_array($error) && in_array($error['type'], [E_USER_ERROR, E_ERROR, E_COMPILE_ERROR, E_RECOVERABLE_ERROR, E_PARSE])) {
                self::handleException(new ErrorException($error['message'], 0, $error['type'], $error['file'], $error['line']));
            }
        }
    }

    /**
     * Get a human readable string representing the given php error code.
     *
     * @param int $errno a php error code, e.g. E_ERROR
     *
     * @return string
     */
    public static function getErrorType($errno)
    {
        switch ($errno) {
            case E_USER_ERROR:
            case E_ERROR:
            case E_COMPILE_ERROR:
            case E_RECOVERABLE_ERROR:
                return 'Fatal error';

            case E_PARSE:
                return 'Parse error';

            case E_USER_WARNING:
            case E_WARNING:
            case E_COMPILE_WARNING:
                return 'Warning';

            case E_USER_NOTICE:
            case E_NOTICE:
                return 'Notice';

            case E_USER_DEPRECATED:
            case E_DEPRECATED:
                return 'Deprecated';

            case E_STRICT:
                return 'Strict';

            default:
                return 'Unknown';
        }
    }
}
