<?php

rex_extension::register('OUTPUT_FILTER', array('rex_extension_debug', 'printStats'));

class rex_extension_debug extends rex_extension
{
  private static
    $calls = array();
    
  static public function registerPoint($extensionPoint, $subject = '', array $params = array (), $read_only = false)
  {
    $timer = new rex_timer();
    $res = parent::registerPoint($extensionPoint, $subject, $params, $read_only);

    self::$calls[] = array($extensionPoint, $timer->stop(3, 1000));
    
    return $res;
  }
  
  static public function printStats($params)
  {
    $debugout = '';
    foreach(self::$calls as $call)
    {
      $debugout .= 'EP: '. $call[0]. ' ' .$call[1] . 'ms<br/>';
    }
    return rex_debug_util::injectHtml($debugout, $params['subject']);
  }
}