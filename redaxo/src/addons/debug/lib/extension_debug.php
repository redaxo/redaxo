<?php

rex_extension::register('OUTPUT_FILTER', array('rex_extension_debug', 'doLog'));

/**
 * Class to monitor extension points via FirePHP
 *
 * @author staabm
 */
class rex_extension_debug extends rex_extension
{
  private static $log      = array();

  /**
   * Extends rex_extension::register() with FirePHP logging
   */
  static public function register($extensionPoint, $callable, array $params = array())
  {
    $timer  = new rex_timer();
    $result = parent::register($extensionPoint, $callable, $params);

    self::$log[] = array(
      'type'     => 'EXT',
      'ep'       => $extensionPoint,
      'callable' => $callable,
      'params'   => $params,
      'result'   => $result,
      'timer'    => $timer->getFormattedDelta(),
    );

    return $result;
  }


  /**
   * Extends rex_extension::registerPoint() with FirePHP logging
   */
  static public function registerPoint($extensionPoint, $subject = '', array $params = array (), $read_only = false)
  {
    $coreTimer = rex::getProperty('timer');
    $absDur    = $coreTimer->getFormattedDelta();

    // start timer for this extensionPoint
    $timer  = new rex_timer();
    $res    = parent::registerPoint($extensionPoint, $subject, $params, $read_only);
    $epDur  = $timer->getFormattedDelta();

    $memory = str_replace(' MiB', '', rex_formatter :: format(memory_get_usage(true), 'bytes', array(3)));

    self::$log[] = array(
      'type'      => 'EP',
      'ep'        => $extensionPoint,
      'started'   => $absDur,
      'duration'  => $epDur,
      'memory'    => $memory,
      'subject'   => $subject,
      'params'    => $params,
      'read_only' => $read_only,
      'result'    => $res,
      'timer'     => $epDur,
      'memory'    => $memory
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

    $ext_stack   = $ep_stack = $log_table = array();
    $errors      = $exec_sum = 0;
    $title_extra = '';
    $counter        = array(
      'ep'       => 0,
      'ext'      => 0,
    );
    $log_table[]    = array(
      '#',
      'Type',
      'ExtensionPoint',
      'Callable',
      'Start',
      'Exec',
      'MiB',
      '$subject',
      '$params',
      'Result',
    );

    // EP FILTER (WHITELIST)
    $filter = explode(',',str_replace(' ','',rex_config::get('debug','ep_log_filter','')));
    if(count($filter)===1 && $filter[0]=='') {
      $filter = null;
    }

    foreach(self::$log as $count=>$entry)
    {
      $i = $count+1;
      switch($entry['type'])
      {
        case'EP':
          $exec_time               = (float) str_replace(array(',',' '),array('. ',''),$entry['duration']);
          $exec_sum                +=$exec_time;
          $counter['ep']++;
          $ext_stack[$entry['ep']] = array();
          $ep_stack[]              = $entry['ep'];
          $entry['callable']       = '–';
          break;

        case'EXT':
          $counter['ext']++;
          $ext_stack[$entry['ep']][$counter['ext']+$counter['ep']] = self::callableAsString($entry['callable']);
          $entry['started'] = $entry['duration'] = $entry['memory'] = $entry['subject'] = '–';
          break;

        default:
          throw new rex_exception('unexpexted type '. $entry['type']);
      }

      if($filter!==null && !in_array($entry['ep'],$filter))
      {
        $title_extra = ' | Filter: '.rex_config::get('debug','ep_log_filter','');
        continue;
      }
      else
      {
        $log_table[] = array(
          $i,                  // #
          $entry['type'],      // Type
          $entry['ep'],        // ExtensionPoint
          $entry['callable'],  // Callable
          $entry['started'],   // Start
          $entry['duration'],  // Exec
          $entry['memory'],    // MiB
          $entry['subject'],   // $subject
          $entry['params'],    // $params
          $entry['result'],    // Result
        );
      }
    }

    // CHECK FOR LATE EXTENSION WHO FAILED TO REGISTER
    foreach ($ext_stack as $ep => $callables)
    {
      foreach ($callables as $log_position => $callable)
      {
        if(in_array($ep, $ep_stack)) // only if EP was actually called at some point..
        {
          $errors++;
          $log_table[$log_position][7] = $log_table[$log_position][8] = $log_table[$log_position][9] = 'Extension didn\'t register!';
          $late_ext = self::$log[$log_position-1];
          $firephp->error('EP Error @ #'.$log_position.': Failed to register callable "'. self::callableAsString($late_ext['callable']) .'", reason: ExtensionPoint "'. $late_ext['ep'] .'" was registered prior to Extension – see EP Log for details');
        }
      }
    }

    $firephp->table('EP Log (EPs: '. $counter['ep'] .' | Extensions: '. $counter['ext'] .' | Errors: '.$errors .' | Total: '.$exec_sum .'ms'.$title_extra.')',$log_table);
  }


  /**
   * Determine type of callable and return string representation
   *
   * @param  mixed   $mixed  some type of callable
   * @return string
   **/
  static public function callableAsString($mixed)
  {
    switch(gettype($mixed))
    {
      case'string':
        return $mixed;
        break;

      case'array':
        foreach ($mixed as $key => $value) {
          $mixed[$key] = self::callableAsString($value);
        }
        return implode('::',$mixed);
        break;

      case'object':
        return get_class($mixed);
        break;

      default:
        return false;
    }
  }
}
