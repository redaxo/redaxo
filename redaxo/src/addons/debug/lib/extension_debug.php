<?php

rex_extension::register('OUTPUT_FILTER', array('rex_extension_debug', 'doLog'));

/**
 * Class to monitor extension points via FirePHP
 *
 * @author staabm
 */
class rex_extension_debug extends rex_extension
{
  static private $log = array();

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
  static public function registerPoint($extensionPoint, $subject = '', array $params = array(), $read_only = false)
  {
    $coreTimer = rex::getProperty('timer');
    $absDur    = $coreTimer->getFormattedDelta();

    // start timer for this extensionPoint
    $timer  = new rex_timer();
    $res    = parent::registerPoint($extensionPoint, $subject, $params, $read_only);
    $epDur  = $timer->getFormattedDelta();

    $memory = rex_formatter :: format(memory_get_usage(true), 'bytes', array(3));

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

    $registered_eps = $log_table = array();
    $counter        = array(
      'ep'       => 0,
      'ext'      => 0,
    );
    $log_table[]    = array(
      'Type',
      'ExtensionPoint',
      'Callable',
      'Start / Dur.',
      'Memory',
      'subject',
      'params',
      'result',
    );

    foreach (self::$log as $count => $entry) {
      switch ($entry['type']) {
        case 'EP':
          $counter['ep']++;
          $registered_eps[] = $entry['ep'];
          $log_table[] = array(
            $entry['type'],      // Type
            $entry['ep'] . ($entry['read_only'] ? ' (readonly)' : ''),        // ExtensionPoint / readonly
            '–',                 // Callable
            $entry['started'] . '/ ' . $entry['duration'] . 'ms',   // Start / Dur.
            $entry['memory'],    // Memory
            $entry['subject'],   // subject
            $entry['params'],    // params
            $entry['result'],    // result
          );
          break;

        case 'EXT':
          $counter['ext']++;

          if (in_array($entry['ep'], $registered_eps)) {
            $firephp->error('EP Timing: Extension "' . $entry['callable'] . '" registered after ExtensionPoint "' . $entry['ep'] . '" !');
          }

          $log_table[] = array(
            $entry['type'],     // Type
            $entry['ep'],       // ExtensionPoint / readonly
            $entry['callable'], // Callable
            '–',                // Start / Dur.
            '–',                // Memory
            '–',                // subject
            $entry['params'],   // params
            $entry['result'],   // result
          );
          break;

        default:
          throw new rex_exception('unexpexted type ' . $entry['type']);
      }
    }

    $firephp->table('EP Log ( EPs: ' . $counter['ep'] . ', Extensions: ' . $counter['ext'] . ' )', $log_table);
  }
}
