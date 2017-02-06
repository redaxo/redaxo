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
            $whoops = new \Whoops\Run();
            $whoops->writeToOutput(false);
            $whoops->allowQuit(false);

            $handler = new \Whoops\Handler\PrettyPageHandler();
            if (ini_get('xdebug.file_link_format')) {
                $handler->setEditor('xdebug');
            }

            $whoops->pushHandler($handler);

            $errPage = $whoops->handleException($exception);
            if (!rex::isSetup() && rex::isBackend() && !rex::isSafeMode()) {
                $logo = '<svg xmlns="http://www.w3.org/2000/svg" width="190px" height="30px" viewBox="0 0 190 30"><path fill="#FFFFFF" d="M147.414,8.221c-0.344,0-5.73,0.003-5.73,0.003c-2.375,0-3.768,2.152-4.281,7.117c-0.571,5.528,1.038,6.839,3.428,6.787c0,0,4.383-0.018,4.983-0.018c2.578,0,3.868-1.401,4.424-6.75C150.776,9.795,150.006,8.221,147.414,8.221z M147.576,1.085c6.356,0,10.716,4.8,9.78,14.12c-0.93,9.264-4.338,14.051-11.097,14.039l-5.618,0.018c-7.919-0.022-11.169-6.19-10.311-14.756c0.813-8.112,4.823-13.417,10.842-13.417L147.576,1.085z M88.31,14.004h11.837l0.239-2.326c0.263-2.215-0.569-3.44-2.919-3.449h-5.445c-2.204,0.074-3.232,1.037-3.548,4.148L88.31,14.004z M99.5,20.268H87.678l-0.888,8.813h-7.095l1.755-17.412c0.727-6.39,4.402-10.456,10.553-10.456h5.471c6.285,0.021,10.614,4.443,9.936,11.176l-1.723,16.691H98.59L99.5,20.268L99.5,20.268z"/><path fill="#FFFFFF" d="M69.432,8.269h-6.758l-1.384,13.73h6.813c2.196,0,4.068-2.377,4.554-7.492C73.008,10.791,71.503,8.269,69.432,8.269z M69.713,1.213c14.65,0,12.27,27.844-1.457,27.844l-14.773,0.001l2.811-27.845H69.713L69.713,1.213z M53.957,8.239L40.618,8.237c-1.725,0-2.939,0.975-3.532,3.49h14.123l-0.631,6.263H36.413c0.11,2.896,1.056,3.845,3.481,3.854c2.037,0.009,8.634,0.106,12.677,0.127l-0.726,7.088H39.672c-7.177,0-11.146-5.596-10.233-14.52C30.402,5.116,34.176,1.21,40.609,1.21l14.05,0.003L53.957,8.239L53.957,8.239z M9.655,13.577h8.266c2.082,0,3.532-1.039,3.532-2.756c0-1.301-1.005-2.543-2.819-2.543c-1.406,0-5.36-0.021-8.44-0.038L9.655,13.577z M23.032,19.573l4.207,9.485h-7.722l-3.735-8.423H8.944l-0.85,8.423H1L3.802,1.215l14.655-0.006c6.585,0,10.478,5.11,10.075,9.686C28.215,14.502,26.893,17.467,23.032,19.573z"/><path fill="#FFFFFF" d="M114.552,1.279l5.167,8.52l5.461-8.52h8.835l-10.004,15.089l8.675,12.667h-8.309l-4.2-6.607l-4.398,6.607h-9.147l9.056-13.276l-9.043-14.479H114.552L114.552,1.279z"/><path fill="#FFFFFF" d="M167.358,21.674l-3.909-0.001c-0.938,0-1.487,0.334-1.746,2.553c-0.229,1.97,0.399,2.474,1.338,2.474c0.732,0,2.414,0.016,3.81,0.008l-0.26,2.581c-1.424,0.008-3.024-0.009-3.726-0.009c-2.691,0-4.008-2.053-3.729-5.312c0.258-3.015,1.581-4.874,4.075-4.874l4.408,0.001L167.358,21.674z M188.539,21.634l-4.229,0.002c-1.084,0-1.028,1.299-0.163,1.299l1.786,0.003c4.288,0,3.99,6.319-0.339,6.319l-5.103-0.008l0.261-2.581l4.935,0.008c0.994,0,0.983-1.383,0.154-1.383l-1.694-0.002c-4.316,0-4.096-6.236,0.33-6.236l4.321-0.002L188.539,21.634L188.539,21.634z M175.543,21.67l-0.769,7.624h-2.595l0.77-7.624l-1.809-0.017l-0.712,7.641h-2.591l0.954-10.255l7.51,0.051c2.297,0.008,3.88,1.641,3.632,4.102l-0.629,6.103h-2.595l0.657-6.363c0.093-0.923-0.276-1.256-1.067-1.26L175.543,21.67L175.543,21.67z"/><path fill="#FFFFFF" d="M165.465,1c2.893,0,5.236,2.345,5.236,5.25c0,2.891-2.344,5.236-5.236,5.236c-2.891,0-5.236-2.345-5.236-5.236C160.229,3.345,162.574,1,165.465,1z M162.703,9.082c1.515,1.553,4.011,1.554,5.539,0c1.557-1.57,1.557-4.109,0-5.679c-1.528-1.554-4.024-1.553-5.539,0c-0.752,0.752-1.175,1.773-1.175,2.846C161.528,7.308,161.95,8.329,162.703,9.082z M166.391,6.713l1.448,2.647h-1.78l-1.122-2.222l-0.272,2.222h-1.517l0.779-6.33h1.903c1.558,0,2.373,0.925,1.917,2.369C167.545,6.075,167.067,6.516,166.391,6.713L166.391,6.713z M165.318,5.761c0.533,0,0.822-0.219,0.935-0.577c0.188-0.528-0.034-0.854-0.652-0.854h-0.318l-0.175,1.432H165.318L165.318,5.761z"/></svg>';
                $style = '
                    <style>
                        .Whoops {
                            padding-top: 50px;
                        }
                        .panel {
                            top: 50px;
                            bottom: 0;
                            height: auto;
                        }
                        .rex-whoops-header {
                            position: fixed;
                            top: 0;
                            left: 0;
                            right: 0;
                            height: 50px;
                            background: #b00;                            
                        }
                        .rex-logo {
                            padding-left: 40px;
                            line-height: 50px;
                        }
                        .rex-logo > svg {
                            vertical-align: middle;
                        }
                        .rex-safemode {
                            position: absolute;
                            top: 20px;
                            right: 40px;
                            color: #fff;
                        }
                    </style>';

                $errPage = str_replace(
                    [
                        '</head>',
                        '</body>'
                    ], [
                        $style . '</head>',
                        '<div class="rex-whoops-header"><div class="rex-logo">' . $logo . '</div><a class="rex-safemode" href="' . rex_url::backendPage('packages', ['safemode' => 1]) . '">activate safe mode</a></div></body>'
                    ],
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
