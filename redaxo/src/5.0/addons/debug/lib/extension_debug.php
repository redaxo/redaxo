<?php

rex_extension::register('OUTPUT_FILTER', array('rex_extension_debug', 'doLog'));

/**
 * Class to monitor extension points
 *
 * @author staabm
 */
class rex_extension_debug extends rex_extension
{
  private static
    $extensionCalls = array(),
    $epCalls = array();

  protected static function invokeExtension($function, $params)
  {
    $timer = new rex_timer();
    $result = parent::invokeExtension($function, $params);
    
    self::$extensionCalls[] = $timer->getFormattedTime(rex_timer::MILLISEC);
    
    return $result;
  }
  
  static public function registerPoint($extensionPoint, $subject = '', array $params = array (), $read_only = false)
  {
    $timer = new rex_timer();
    $res = parent::registerPoint($extensionPoint, $subject, $params, $read_only);

    self::$epCalls[] = array($extensionPoint, $timer->getFormattedTime(rex_timer::MILLISEC), self::$extensionCalls);
    self::$extensionCalls = array();

    return $res;
  }

  static public function doLog($params)
  {
    $firephp = FirePHP::getInstance(true);
    $firephp->group(__CLASS__);
    foreach(self::$epCalls as $call)
    {
      
      // when a extension takes longer than 5ms, send a warning
      if(strtr($call[1], ',', '.') > 5)
      {
        $detail = '';
        if(!empty($call[2]))
        {
          $detail = json_encode($call[2]);
        }
        
        $firephp->warn('EP: '. $call[0]. ' ' .$call[1] . 'ms '. $detail);
      }
      else
      {
        $firephp->log('EP: '. $call[0]. ' ' .$call[1] . 'ms');
      }
    }
    $firephp->groupEnd();
  }
}