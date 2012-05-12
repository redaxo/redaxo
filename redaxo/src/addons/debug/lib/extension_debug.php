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
    $log            = array();

  protected static function invokeExtension($function, $params)
  {
    $timer  = new rex_timer();
    $result = parent::invokeExtension($function, $params);

    self::$log[] = array(
                         'type'     =>'EXT',
                         'ep'       =>$params['extension_point'],
                         'callable' =>$function,
                         'params'   =>$params,
                         'result'   =>$result,
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

    self::$log[] = array(
                         'type'      =>'EP',
                         'ep'        =>$extensionPoint,
                         'started'   =>$absDur,
                         'duration'  =>$epDur,
                         'memory'    =>$memory,
                         'subject'   =>$subject,
                         'params'    =>$params,
                         'read_only' =>$read_only,
                         'result'    =>$res,
                         'timer'     =>$epDur,
                         'memory'    =>$memory
                         );

    return $res;
  }

  static public function doLog($params)
  {
    $firephp = FirePHP::getInstance(true);

    $counter        = array('ep'=>0,'ext'=>0,'timing_err'=>0);
    $registered_eps = $log_table = array();
    $log_table[]    = array('#','Type','Timing','ExtensionPoint','Callable','Started','Duration','Memory','subject','params','result','read_only');

    foreach(self::$log as $k=>$v)
    {
      $i = $k+1;
      switch($v['type'])
      {
        case'EP':
          $counter['ep']++;
          $registered_eps[] = $v['ep'];
          $log_table[] = array($i,$v['type'],'–'     ,$v['ep']        ,'–'       ,$v['started'],$v['duration'],$v['memory'],$v['subject'],$v['params'],$v['result'],$v['read_only']);
          break;

        case'EXT':
          $counter['ext']++;
          $timing = 'ok';
          if(in_array($v['ep'],$registered_eps)) {
            $timing = 'late';
            $counter['timing_err']++;
          }
          $log_table[] = array($i,$v['type'],$timing ,$v['ep']        ,$v['callable'],'–'      ,'–'       ,'–'     ,'–'      ,$v['params'],$v['result'],'–');
          break;
      }
    }
    $firephp->table('EP LOG ( EPs: '.$counter['ep'].', Extensions: '.$counter['ext'].', Late Extensions: '.$counter['timing_err'].' )',$log_table);
  }
}
