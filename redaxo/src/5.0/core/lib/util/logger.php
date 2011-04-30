<?php

/**
 * Simple Logger class
 *
 * @author staabm
 */
class rex_logger {
  private static
    $instance;

  private
    $file,
    $handle,
    $registered;

  /**
   * Constructs a logger
   * @param String $file file in which will be logged
   */
  protected function __construct($file)
  {
    $this->file = $file;
    $this->open();
  }

  /**
   * Retrieves the logger instance
   *
   * @return rex_logger the logger instance
   */
  static public function getInstance()
  {
    global $REX;

    if (!isset(self::$instance))
    {
      self::$instance = new rex_logger(rex_path::generated('files/system.log'));
    }

    return self::$instance;
  }

  /**
   * Registers the logger instance as php-error/exception handler
   */
  static public function register()
  {
    $logger = self::getInstance();

    if($logger->registered) return;
      $logger->registered = true;

    set_error_handler(array($logger, 'logError'));
    set_exception_handler(array($logger, 'logException'));
    register_shutdown_function(array($logger, 'shutdown'));
    
    $logger->open();
  }

  /**
   * Unregisters the logger instance as php-error/exception handler
   */
  static public function unregister()
  {
    $logger = self::getInstance();

    if(!$logger->registered) return;
    $logger->registered = false;

    restore_error_handler();
    restore_exception_handler();
    // unregister of shutdown function is not possible
    
    $logger->close();
  }

  /**
   * Logs the given Exception
   *
   * @param Exception $exception The Exception to log
   */
  public function logException(Exception $exception)
  {
    $this->logError($exception->getCode(), $exception->getMessage(), $exception->getFile(), $exception->getLine());
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
  public function logError($errno, $errstr, $errfile, $errline, array $errcontext = null, $printError = true)
  {
    if(!is_int($errno))
    {
      throw new rexException('Expecting $errno to be integer, but '. gettype($errno) .' given!');
    }
    if(!is_string($errstr))
    {
      throw new rexException('Expecting $errstr to be string, but '. gettype($errstr) .' given!');
    }
    if(!is_string($errfile))
    {
      throw new rexException('Expecting $errfile to be string, but '. gettype($errfile) .' given!');
    }
    if(!is_int($errline))
    {
      throw new rexException('Expecting $errline to be integer, but '. gettype($errline) .' given!');
    }
    if(!is_bool($printError))
    {
      throw new rexException('Expecting $printError to be boolean, but '. gettype($printError) .' given!');
    }

    $errorType = '<b>'. $this->getErrorType($errno) .'</b>';

    $msg = "$errstr in <b>$errfile</b> on line <b>$errline</b><br />\n";

    // errors which should be reported regarding error_reporting() will be echo'ed to the end-user
    if ($printError && ini_get('display_errors') && (error_reporting() & $errno) == $errno) {
      echo $errorType .': '. $msg;
    }

    $this->log($errorType .'['. $errno .']: '. $msg);

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
  public function log($message)
  {
    if(!is_string($message))
    {
      throw new rexException('Expecting $message to be string, but '. gettype($message) .' given!');
    }

    if(is_resource($this->handle))
    {
      fwrite($this->handle, date('r') .'<br />'. $message);
    }
  }

  /**
   * Prepares the logifle for later use
   */
  public function open()
  {
    // check if already opened
    if(!$this->handle)
    {
      $this->handle = fopen($this->file, 'ab');
    }

    if(!$this->handle)
    {
      echo 'Error while creating logfile '. $this->file;
      exit();
    }
  }

  /**
   * Closes the logfile. The logfile is not be able to log further message after beeing closed.
   *
	 * You dont need to close the logfile manually when it was registered during the request.
   */
  public function close()
  {
    if(is_resource($this->handle))
    {
      fclose($this->handle);
    }
  }

  /**
   * Shutdown-handler which is called at the very end of the request
   */
  public function shutdown()
  {
    if($this->registered)
    {
      $error = error_get_last();
      if(is_array($error))
      {
        $this->logError($error['type'], $error['message'], $error['file'], $error['line'], null, false);
      }
    }

    $this->close();
  }

  /**
   * Get a human readable string representing the given php error code
   *
   * @param int $errno a php error code, e.g. E_ERROR
   */
  private function getErrorType($errno)
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