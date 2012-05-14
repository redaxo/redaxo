<?php

rex_extension::register('OUTPUT_FILTER', array('rex_sql_debug', 'doLog'));

/**
 * Class to monitor sql queries
 *
 * @author staabm
 */
class rex_sql_debug extends rex_sql
{
  private static $log      = array();
  private static $errors   = 0;
  private static $exec_sum = 0;


  /**
   * Extends rex_sql::setQuery() with logging
   */
  public function setQuery($qry, array $params = array())
  {
    try {
      $timer     = new rex_timer();
      $ret       = parent::setQuery($qry, $params);
      $exec_time = $timer->getFormattedDelta(rex_timer::MILLISEC);
      $trace     = self::examine_trace(debug_backtrace());
      $error     = '';
    } catch (Exception $e) {
      $exec_time = $timer->getFormattedDelta(rex_timer::MILLISEC);
      $trace     = self::examine_trace(debug_backtrace());
      $error     = $e->getMessage();
      $ret       = false;
      self::$errors++;

      // PROMPT ERROR SEPARATELY
      FB::error('SQL Error @ Query #'.(count(self::$log)+1).': "'.$qry.'" – see SQL Log for details.');
    }

    self::add_to_log($exec_time,$qry,$trace,$error);

    return $ret;
  }


  /**
   * Extends rex_sql::execute() with logging
   */
  public function execute(array $params = array())
  {
    $qry = $this->stmt->queryString;

    try {
      $timer     = new rex_timer();
      $res       = parent::execute($params);
      $exec_time = $timer->getFormattedDelta(rex_timer::MILLISEC);
      $trace     = self::examine_trace(debug_backtrace());
      $error     = '';
    } catch (Exception $e) {
      $exec_time = $timer->getFormattedDelta(rex_timer::MILLISEC);
      $trace     = self::examine_trace(debug_backtrace());
      $error     = $e->getMessage();
      $res       = null;
      self::$errors++;

      FB::error('SQL Error @ Query #'.(count(self::$log)+1).': "'.$qry.'" – see SQL Log for details.');
    }

    self::add_to_log($exec_time,$qry,$trace,$error);

    return $res;
  }


  /**
   * send log to FirePHP
   * @param  array  $params  EP params
   * @return void
   **/
  static public function doLog($params)
  {
    if(!empty(self::$log))
    {
      $tbl = array();
      $tbl[] = array(
        '#',
        'Rows/Error',
        'ms',
        'Query',
        'File (Line)',
        );

      foreach(self::$log as $i => $qry)
      {
        // IF ERROR -> DUMP ERROR IN ROWS COLUMN
        if($qry['error']!=''){
          $qry['rows'] = $qry['error'];
          $qry['error'] = '';
        }
        // APPEND LINE AFTER FILEPATH
        $qry['file'] = $qry['file'].' ('.$qry['line'].')';

        $tbl[] = array($i+1,$qry['rows'],$qry['time'],$qry['query'],$qry['file']);
      }

      $firephp = FirePHP::getInstance(true);
      $firephp->table('SQL Log ( Queries: '.count(self::$log).', Errors: '.self::$errors.', Total: '.self::$exec_sum.'ms )', $tbl);
    }
  }


  /**
   * process query details & store to log
   * @param  string  $exec_time  Execution time (ms)
   * @param  string  $qry        Query
   * @param  array   $trace      Backtrace
   * @return void
   **/
  public function add_to_log($exec_time,$qry,$trace,$error)
  {
    $exec_time = (float) str_replace(array(',',' '),array('.',''),$exec_time); // todo: smarter..

    // PROMPT SLOW QUERY SEPARATELY
    if($exec_time > 5) {
      FB::info('Slow Execution @ Query #'.(count(self::$log)+1).': "'.$qry.'" took '.$exec_time.'ms – see SQL Log for details.');
    }

    self::$exec_sum+=$exec_time;
    self::$log[] = array(
      'rows'  =>$this->getRows(),
      'time'  =>$exec_time,
      'query' =>$qry,
      'file'  =>str_replace(rex_path::base(),'../',$trace['file']),
      'line'  =>$trace['line'],
      'error' =>$error,
      );
  }


  /**
   * find file & line in backtrace
   * @param  array  $trace  Backtrace
   * @return array          File & Line
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
