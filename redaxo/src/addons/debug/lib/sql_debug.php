<?php

rex_extension::register('OUTPUT_FILTER', array('rex_sql_debug', 'doLog'));

/**
 * Class to monitor sql queries
 *
 * @author staabm
 */
class rex_sql_debug extends rex_sql
{
  private static $log    = array();
  private static $errors = 0;


  /**
   * Extends rex_sql::setQuery() with logging
   */
  public function setQuery($qry, array $params = array())
  {
    try {
      $ret   = parent::setQuery($qry, $params);
      $timer = new rex_timer();
      $trace = self::examine_trace(debug_backtrace());
      $error = '';
    } catch (Exception $e) {
      $timer = new rex_timer();
      $trace = self::examine_trace(debug_backtrace());
      $error = $e->getMessage();
      $ret   = false;
      self::$errors++;

      FB::error('SQL Error: '.$error.' – see SQL Log for details');
    }

    self::add_to_log($timer,$qry,$trace,$error);

    return $ret;
  }


  /**
   * Extends rex_sql::execute() with logging
   */
  public function execute(array $params = array())
  {
    $qry   = $this->stmt->queryString;

    try {
      $timer = new rex_timer();
      $res   = parent::execute($params);
      $trace = self::examine_trace(debug_backtrace());
      $error = '';
    } catch (Exception $e) {
      $timer = new rex_timer();
      $trace = self::examine_trace(debug_backtrace());
      $error = $e->getMessage();
      $res   = null;
      self::$errors++;

      FB::error('SQL Error: '.$error.' – see SQL Log for details');
    }

    self::add_to_log($timer,$qry,$trace,$error);

    return $res;
  }


  /**
   * send log to FirePHP
   * @param  array ($params) EP params
   * @return void
   **/
  static public function doLog($params)
  {
    if(!empty(self::$log))
    {
      $tbl = array();
      $tbl[] = array(
        '#',
        'rows',
        'ms',
        'query',
        'file',
        'line',
        'error'
        );

      foreach(self::$log as $i => $qry)
      {
        // when a extension takes longer than 5ms, send a warning
        $late_notice = strtr($qry['time'], ',', '.') > 5 ? '! SLOW: ' : '' ;

        $tbl[] = array($i+1,$qry['rows'],$late_notice.$qry['time'],$qry['query'],$qry['file'],$qry['line'],$qry['error']);
      }

      $firephp = FirePHP::getInstance(true);
      $firephp->table('SQL Log ( Queries: '.count(self::$log).', Errors: '.self::$errors.' )', $tbl);
    }
  }


  /**
   * process query details & store to log
   * @param  string ($timer) timer
   * @param  string ($qry) query
   * @param  array ($trace) backtrace
   * @return void
   **/
  public function add_to_log($timer,$qry,$trace,$error)
  {
    self::$log[] = array(
      'rows'  =>$this->getRows(),
      'time'  =>$timer->getFormattedTime(rex_timer::MILLISEC),
      'query' =>$qry,
      'file'  =>str_replace(rex_path::base(),'../',$trace['file']),
      'line'  =>$trace['line'],
      'error' =>$error,
      );
  }


  /**
   * find file & line in backtrace
   * @param  array ($trace) backtrace
   * @return array file & line
   **/
  public function examine_trace($trace=null)
  {
    if(!$trace || !is_array($trace)) {
      return array('file'=>null,'line'=>null);
    }

    for( $i=0 ; $trace && $i<sizeof($trace) ; $i++ ) {
        if (isset($trace[$i]['file']) && strpos($trace[$i]['file'], 'sql.php') === false && strpos($trace[$i]['file'], 'sql_debug.php') === false) {
            $file = $trace[$i]['file'];
            $line = $trace[$i]['line'];
            break;
        }
    }
    return array('file'=>$file,'line'=>$line);
  }

}
