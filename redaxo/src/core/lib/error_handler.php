<?php

abstract class rex_error_handler
{
  static private $registered;

  /**
   * Registers the class as php-error/exception handler
   */
  static public function register()
  {
    if (self::$registered)
      return;

    self::$registered = true;

    set_error_handler(array(__CLASS__, 'handleError'));
    set_exception_handler(array(__CLASS__, 'handleException'));
    register_shutdown_function(array(__CLASS__, 'shutdown'));
  }

  /**
   * Unregisters the logger instance as php-error/exception handler
   */
  static public function unregister()
  {
    if (!self::$registered)
      return;

    self::$registered = false;

    restore_error_handler();
    restore_exception_handler();
    // unregister of shutdown function is not possible
  }

  /**
   * Handles the given Exception
   *
   * @param Exception $exception The Exception to handle
   */
  static public function handleException(Exception $exception)
  {
    rex_logger::logException($exception);

    while (ob_get_level()) {
      ob_end_clean();
    }

    if (($user = rex_backend_login::createUser()) && $user->isAdmin()) {
      // TODO add a beautiful error page with usefull debugging info
      $buf = '';
      $buf .= '<pre>';
      $buf .= 'Exception thrown in ' . $exception->getFile() . ' on line ' . $exception->getLine() . "\n\n";
      $buf .= '<b>' . get_class($exception) . ': ' . $exception->getMessage() . "</b>\n\n";
      $buf .= $exception->getTraceAsString();
      $buf .= '</pre>';
    } else {
      // TODO small error page, without debug infos
      $buf = 'Oooops, an internal error occured!';
    }

    rex_response::setStatus(rex_response::HTTP_INTERNAL_ERROR);
    rex_response::send($buf);
    exit;
  }

  /**
   * Handles a error message
   *
   * @param integer $errno The error code to handle
   * @param string  $errstr The error message
   * @param string  $errfile The file in which the error occured
   * @param integer $errline The line of the file in which the error occured
   */
  static public function handleError($errno, $errstr, $errfile, $errline)
  {
    if (in_array($errno, array(E_USER_ERROR, E_ERROR, E_COMPILE_ERROR, E_RECOVERABLE_ERROR, E_PARSE))) {
      throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
    } else {
      if (ini_get('display_errors') && (error_reporting() & $errno) == $errno) {
        echo '<b>' . self::getErrorType($errno) . "</b>: $errstr in <b>$errfile</b> on line <b>$errline</b><br />";
      }
      if (error_reporting() == 0) {
        rex_logger::logError($errno, $errstr, $errfile, $errline);
      }
    }
  }

  /**
   * Shutdown-handler which is called at the very end of the request
   */
  static public function shutdown()
  {
    if (self::$registered) {
      $error = error_get_last();
      if (is_array($error)) {
        self::handleException(new ErrorException($error['message'], 0, $error['type'], $error['file'], $error['line']));
      }
    }
  }

  /**
   * Get a human readable string representing the given php error code
   *
   * @param int $errno a php error code, e.g. E_ERROR
   */
  static public function getErrorType($errno)
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
