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
      self::$instance = new rex_logger($REX['SRC_PATH'] .'/generated/files/system.log');
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
    register_shutdown_function(array($logger, 'close'));
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

  public function logError($errno, $errstr, $errfile, $errline)
  {
    $msg = "$errstr in file <span class='rex-err-file'>$errfile</span> on line <span class='rex-err-line'>$errline</span><br />\n";
    
    // errors which should be reported regarding error_reporting() will be echo'ed to the end-user
    if (error_reporting() & $errno) {
      echo $msg;
    }
    
    $msg = '['. $errno .'] '. $msg;
    
    switch ($errno) {
      case E_USER_ERROR:
      case E_ERROR :
        $this->log("<b>ERROR</b>". $msg);
        exit(1);
        break;

      case E_USER_WARNING:
      case E_WARNING:
        $this->log("<b>WARNING</b>". $msg);
        break;

      case E_USER_NOTICE:
      case E_NOTICE:
        $this->log("<b>NOTICE</b>". $msg);
        break;

      case E_STRICT:
        $this->log("<b>STRICT</b>". $msg);
        break;

      default:
        $this->log("<b>UNKOWN:</b>". $msg);
        break;
    }
  }

  public function log($message)
  {
    fwrite($this->handle, date('r') .'<br />'. $message);
  }

  public function open()
  {
    $this->handle = fopen($this->file, 'ab');

    // TODO handle error while filecreation
  }

  public function close()
  {
    if($this->handle)
    {
      fclose($this->handle);
    }
  }
}