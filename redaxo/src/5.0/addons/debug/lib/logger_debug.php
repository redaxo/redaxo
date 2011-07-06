<?php

/**
 * Class to monitor extension points
 *
 * @author staabm
 */
class rex_logger_debug extends rex_logger
{
  /**
  * Logs the given message
  *
  * @param String $message the message to log
  */
  static public function log($message)
  {
    if(!empty($message))
    {
      $firephp = FirePHP::getInstance(true);
      $firephp->log($message);
    }
  }  
}
