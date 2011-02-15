<?php

class rex_logger {
  private static
    $instance;

  private
    $file,
    $handle,
    $registered;


  protected function __construct($file)
  {
    $this->file = $file;
    $this->open();
  }

  static public function getInstance()
  {
    global $REX;

    if (!isset(self::$instance))
    {
      // TODO: Move rex_logger init to the very beginning of the boostrap, so we are able to inject the path by parameter!
      self::$instance = new rex_logger(rex_path::generated('files/system.log'));
    }

    return self::$instance;
  }

  static public function register()
  {
    $logger = self::getInstance();

    if($logger->registered) return;
      $logger->registered = true;

    set_error_handler(array($logger, 'logError'));
    set_exception_handler(array($logger, 'logException'));
    register_shutdown_function(array($logger, 'shutdown'));
  }

  static public function unregister()
  {
    $logger = self::getInstance();

    if(!$logger->registered) return;
    $logger->registered = false;

    restore_error_handler();
    restore_exception_handler();
    // unregister of shutdown function is not possible
  }


  public function logException(Exception $exception)
  {
    $this->logError($exception->getCode(), $exception->getMessage(), $exception->getFile(), $exception->getLine());
  }

  public function logError($errno, $errstr, $errfile, $errline, $printError = true)
  {
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

  public function log($message)
  {
    fwrite($this->handle, date('r') .'<br />'. $message);
  }

  public function open()
  {
    $this->handle = fopen($this->file, 'ab');

    if(!$this->handle)
    {
      echo 'Error while creating logfile '. $this->file;
      exit();
    }
  }

  public function close()
  {
    if($this->handle)
    {
      fclose($this->handle);
    }
  }

  public function shutdown()
  {
    if($this->registered)
    {
      $error = error_get_last();
      if(is_array($error))
      {
        $this->logError($error['type'], $error['message'], $error['file'], $error['line'], false);
      }
    }

    $this->close();
  }

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