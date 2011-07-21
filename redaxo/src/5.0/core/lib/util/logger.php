<?php

/**
 * Simple Logger class
 *
 * @author staabm
 */
abstract class rex_logger extends rex_factory
{
  private static
    $file,
    $handle,
    $registered;

  /**
   * Registers the logger instance as php-error/exception handler
   */
  static public function register()
  {
    if(self::$registered)
      return;

    self::$registered = true;
    self::$file = rex_path::cache('system.log');

    set_error_handler(array(__CLASS__, 'logError'));
    set_exception_handler(array(__CLASS__, 'logException'));
    register_shutdown_function(array(__CLASS__, 'shutdown'));

    self::open();
  }

  /**
   * Unregisters the logger instance as php-error/exception handler
   */
  static public function unregister()
  {
    if(!self::$registered)
      return;

    self::$registered = false;

    restore_error_handler();
    restore_exception_handler();
    // unregister of shutdown function is not possible

    self::close();
  }

  /**
   * Logs the given Exception
   *
   * @param Exception $exception The Exception to log
   */
  static public function logException(Exception $exception)
  {
    self::logError($exception->getCode(), $exception->getMessage(), $exception->getFile(), $exception->getLine());
  }

  /**
   * Logs a error message
   *
   * @param integer $errno The error code to log
   * @param string  $errstr The error message
   * @param string  $errfile The file in which the error occured
   * @param integer $errline The line of the file in which the error occured
   * @param array   $errcontext Array that points to the active symbol table at the point the error occurred.
   * @param boolean $printError Flag to indicate whether the error should be printed, or not
   */
  static public function logError($errno, $errstr, $errfile, $errline, array $errcontext = null, $printError = true)
  {
    if(!is_int($errno))
    {
      throw new rex_exception('Expecting $errno to be integer, but '. gettype($errno) .' given!');
    }
    if(!is_string($errstr))
    {
      throw new rex_exception('Expecting $errstr to be string, but '. gettype($errstr) .' given!');
    }
    if(!is_string($errfile))
    {
      throw new rex_exception('Expecting $errfile to be string, but '. gettype($errfile) .' given!');
    }
    if(!is_int($errline))
    {
      throw new rex_exception('Expecting $errline to be integer, but '. gettype($errline) .' given!');
    }
    if(!is_bool($printError))
    {
      throw new rex_exception('Expecting $printError to be boolean, but '. gettype($printError) .' given!');
    }

    $errorType = '<b>'. self::getErrorType($errno) .'</b>';

    $msg = "$errstr in <b>$errfile</b> on line <b>$errline</b><br />";

    // errors which should be reported regarding error_reporting() will be echo'ed to the end-user
    if ($printError && ini_get('display_errors') && (error_reporting() & $errno) == $errno) {
      echo $errorType .': '. $msg;
    }

    self::log($errorType .'['. $errno .']: '. $msg, $errno);

    if(in_array($errno, array(E_USER_ERROR, E_ERROR, E_COMPILE_ERROR, E_RECOVERABLE_ERROR)))
    {
      exit(1);
    }
  }

  /**
   * Logs the given message
   *
   * @param String $message the message to log
   */
  static public function log($message, $errno = E_USER_ERROR)
  {
    if(static::hasFactoryClass())
    {
      return static::callFactoryClass(__FUNCTION__, func_get_args());
    }

    if(!is_string($message))
    {
      throw new rex_exception('Expecting $message to be string, but '. gettype($message) .' given!');
    }

    if(is_resource(self::$handle))
    {
      fwrite(self::$handle, date('r') .'<br />'. $message. "\n");
    }
  }

  /**
   * Prepares the logifle for later use
   */
  static public function open()
  {
    // check if already opened
    if(!self::$handle)
    {
      self::$handle = fopen(self::$file, 'ab');
    }

    if(!self::$handle)
    {
      echo 'Error while creating logfile '. self::$file;
      exit();
    }
  }

  /**
   * Closes the logfile. The logfile is not be able to log further message after beeing closed.
   *
	 * You dont need to close the logfile manually when it was registered during the request.
   */
  static public function close()
  {
    if(is_resource(self::$handle))
    {
      fclose(self::$handle);
    }
  }

  /**
   * Shutdown-handler which is called at the very end of the request
   */
  static public function shutdown()
  {
    if(self::$registered)
    {
      $error = error_get_last();
      if(is_array($error))
      {
      	try {
      		self::logError($error['type'], $error['message'], $error['file'], $error['line'], null, false);
      	}catch (Exception $e ) {
      		// echo $e->getMessage();
      	}
      }
    }

    self::close();
  }

  /**
   * Get a human readable string representing the given php error code
   *
   * @param int $errno a php error code, e.g. E_ERROR
   */
  static private function getErrorType($errno)
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