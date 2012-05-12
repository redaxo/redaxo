<?php

rex_extension::register('OUTPUT_FILTER', array('rex_extension_debug', 'doLog'));

/**
 * Class to monitor extension points via FirePHP
 *
 * @author staabm
 */
class rex_extension_debug extends rex_extension
{
  private static $log = array();


  /**
  * Extends rex_extension::register() with FirePHP logging
  */
  static public function register($extensionPoint, $callable, array $params = array())
  {
    $timer  = new rex_timer();
    $result = parent::register($extensionPoint, $callable, $params);

    self::$log[] = array(
                         'type'     =>'EXT',
                         'ep'       =>$extensionPoint,
                         'callable' =>$callable,
                         'params'   =>$params,
                         'result'   =>$result,
                         'timer'    =>$timer->getFormattedTime(rex_timer::MILLISEC),
                         );

    return $result;
  }


  /**
  * Extends rex_extension::registerPoint() with FirePHP logging
  */
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


  /**
   * process log & send as FirePHP table
   * @param array $params EP Params
   * @return void
   */
  static public function doLog($params)
  {
    $firephp = FirePHP::getInstance(true);

    $registered_eps = $log_table = array();
    $counter        = array(
                            'ep'       =>0,
                            'ext'      =>0,
                            'late_ext' => array(),
                            );
    $log_table[]    = array(
                            '#',
                            'Type',
                            'Timing',
                            'ExtensionPoint',
                            'Callable',
                            'Started',
                            'Duration',
                            'Memory',
                            'subject',
                            'params',
                            'result',
                            'read_only'
                            );

    foreach(self::$log as $count=>$entry)
    {
      $i = $count+1;
      switch($entry['type'])
      {
        case'EP':
          $counter['ep']++;
          $registered_eps[] = $entry['ep'];
          $log_table[] = array(
                               $i,                  // #
                               $entry['type'],      // Type
                               '–',                 // Timing
                               $entry['ep'],        // ExtensionPoint
                               '–',                 // Callable
                               $entry['started'],   // Started
                               $entry['duration'],  // Duration
                               $entry['memory'],    // Memory
                               $entry['subject'],   // subject
                               $entry['params'],    // params
                               $entry['result'],    // result
                               $entry['read_only']  // read_only
                               );
          break;

        case'EXT':
          $counter['ext']++;
          $timing = 'ok';

          if(in_array($entry['ep'],$registered_eps))
          {
            $timing = 'late';
            $counter['late_ext'][$entry['callable']] = $entry['ep'];
            $firephp->error('EP Timing: Extension "'.$entry['callable'].'" registered after ExtensionPoint "'.$entry['ep'].'" !');
          }

          $log_table[] = array(
                               $i,                 // #
                               $entry['type'],     // Type
                               $timing,            // Timing
                               $entry['ep'],       // ExtensionPoint
                               $entry['callable'], // Callable
                               '–',                // Started
                               '–',                // Duration
                               '–',                // Memory
                               '–',                // subject
                               $entry['params'],   // params
                               $entry['result'],   // result
                               '–'                 // read_only
                               );
          break;

        default:
          // can't actually happen..
      }
    }

    $firephp->table('EP LOG ( EPs: '.$counter['ep'].', Extensions: '.$counter['ext'].' )',$log_table);
  }
}
