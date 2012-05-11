<?php

rex_extension::register('OUTPUT_FILTER', array('rex_sql_debug', 'doLog'));

/**
 * Class to monitor sql queries
 *
 * @author staabm
 */
class rex_sql_debug extends rex_sql
{
  private static $queries = array();
  private static $errors  = 0;


  /**
   * Extends rex_sql::setQuery() with logging
   */
  public function setQuery($qry, array $params = array())
  {
    $ret   = parent::setQuery($qry, $params);
    $timer = new rex_timer();
    $trace = self::examine_trace(debug_backtrace());

    self::add_to_log($timer,$qry,$trace);

    return $ret;
  }


  /**
   * Extends rex_sql::execute() with logging
   */
  public function execute(array $params = array())
  {
    $qry   = $this->stmt->queryString;

    $timer = new rex_timer();
    $res   = parent::execute($params);
    $trace = self::examine_trace(debug_backtrace());

    self::add_to_log($timer,$qry,$trace);

    return $res;
  }


  /**
   * send log to FirePHP
   * @param  array ($params) EP params
   * @return void
   **/
  static public function doLog($params)
  {
    if(!empty(self::$queries))
    {
      $tbl = array();
      $tbl[] = array('#','rows','ms','query','file','line');
      $i = 1;

      foreach(self::$queries as $qry)
      {
        // when a extension takes longer than 5ms, send a warning
        if(strtr($qry['time'], ',', '.') > 5)
        {
          $tbl[] = array($i,$qry['rows'],'! SLOW: '.$qry['time'],$qry['query'],$qry['file'],$qry['line']);
        }
        else
        {
          $tbl[] = array($i,$qry['rows'],$qry['time'],$qry['query'],$qry['file'],$qry['line']);
        }
        $i++;
      }

      $firephp = FirePHP::getInstance(true);
      $firephp->table(__CLASS__.' ('.count(self::$queries).' queries, '.self::$errors.' errors)', $tbl);
    }
  }


  /**
   * process query details & store to log
   * @param  string ($timer) timer
   * @param  string ($qry) query
   * @param  array ($trace) backtrace
   * @return void
   **/
  public function add_to_log($timer,$qry,$trace)
  {
    self::$queries[] = array(
      'rows'  =>$this->getRows(),
      'time'  =>$timer->getFormattedTime(rex_timer::MILLISEC),
      'query' =>$qry,
      'file'  =>str_replace(rex_path::base(),'../',$trace['file']),
      'line'  =>$trace['line'],
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
        if (isset($trace[$i]['file']) && strpos($trace[$i]['file'], 'sql.php') === false) {
            $file = $trace[$i]['file'];
            $line = $trace[$i]['line'];
            break;
        }
    }
    return array('file'=>$file,'line'=>$line);
  }

}
