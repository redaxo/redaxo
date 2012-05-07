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
    $epCalls        = array(),
    $log            = array();

  protected static function invokeExtension($function, $params)
  {
    $timer  = new rex_timer();
    $result = parent::invokeExtension($function, $params);

    self::$extensionCalls[] = $timer->getFormattedTime(rex_timer::MILLISEC);
    self::$log[] = array(
                         'type'     =>'EXT',
                         'ep'       =>$params['extension_point'],
                         'callable' =>$function,
                         '$params'  =>$params,
                         '$result'  =>$result,
                         'timer'    =>$timer->getFormattedTime(rex_timer::MILLISEC),
                         );

    return $result;
  }

  static public function registerPoint($extensionPoint, $subject = '', array $params = array (), $read_only = false)
  {
    $coreTimer = rex::getProperty('timer');
    $absDur    = $coreTimer->getFormattedTime(rex_timer::MILLISEC);

    // start timer for this extensionPoint
    $timer  = new rex_timer();
    $res    = parent::registerPoint($extensionPoint, $subject, $params, $read_only);
    $epDur  = $timer->getFormattedTime(rex_timer::MILLISEC);

    $memory = rex_formatter :: format(memory_get_usage(true), 'filesize', array(3));

    self::$epCalls[] = array($extensionPoint, $epDur, self::$extensionCalls, $absDur, $memory);
    self::$extensionCalls = array();
    self::$log[] = array(
                         'type'       =>'EP',
                         'ep'         =>$extensionPoint,
                         '$subject'   =>$subject,
                         '$params'    =>$params,
                         '$read_only' =>$read_only,
                         '$result'    =>$res,
                         'timer'      =>$epDur,
                         'memory'     =>$memory,
                         );

    return $res;
  }

  static public function doLog($params)
  {
    $firephp     = FirePHP::getInstance(true);
    $firephp->group(__CLASS__);
    $ext_table   = array();
    $log_table[] = array('ExtensionPoint','Started','Duration','Memory','remark');

    foreach(self::$epCalls as $call)
    {

      // when a extension takes longer than 5ms, send a warning
      if(strtr($call[1], ',', '.') > 5)
      {
        $detail = '';
        if(!empty($call[2]))
        {
          $detail = '; extensions '. json_encode($call[2]);
        }

        $firephp->warn('EP: '. $call[0]. ' (started ' .$call[3] . 'ms, duration ' .$call[1] . 'ms; '. $call[4] .')'. $detail);
        $log_table[] = array($call[0],$call[3].'ms',$call[1].'ms',$call[4],'slow');
      }
      else
      {
        $firephp->log('EP: '. $call[0]. ' (started ' .$call[3] . 'ms, duration ' .$call[1] . 'ms; '. $call[4] .')');
        $log_table[] = array($call[0],$call[3].'ms',$call[1].'ms',$call[4],'–');
      }
    }
    $firephp->groupEnd();
    $firephp->table('ExtensionPoints',$log_table);

    // EP LOG ALTERNATE STYLE ALA FIREPHP ADDON..
    $registered_eps = $log_table = array();
    $log_table[]    = array('#','Type','Timing','ExtensionPoint','Callable','$subject','$params','$result','$read_only');
    foreach(self::$log as $k=>$v)
    {
      $i = $k+1;
      switch($v['type'])
      {
        case'EP':
          $registered_eps[] = $v['ep'];
          $log_table[] = array($i,$v['type'],'–',$v['ep'],'–',$v['$subject'],$v['$params'],$v['$result'],$v['$read_only']);
          break;

        case'EXT':
          $timing = in_array($v['ep'],$registered_eps) ? 'late' : 'ok';
          $log_table[] = array($i,$v['type'],$timing,$v['ep'],$v['callable'],'–',$v['$params'],$v['$result'],'–');
          break;
      }
    }
    $firephp->table('EP LOG ('.$i.' calls)',$log_table);
  }
}
