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
    $calls = array();

  static public function registerPoint($extensionPoint, $subject = '', array $params = array (), $read_only = false)
  {
    $timer = new rex_timer();
    $res = parent::registerPoint($extensionPoint, $subject, $params, $read_only);

    self::$calls[] = array($extensionPoint, $timer->getFormattedTime(rex_timer::MILLISEC));

    return $res;
  }

  static public function doLog($params)
  {
    $firephp = FirePHP::getInstance(true);
    $firephp->group(__CLASS__);
    foreach(self::$calls as $call)
    {
      $firephp->log('EP: '. $call[0]. ' ' .$call[1] . 'ms');
    }
    $firephp->groupEnd();
  }
}