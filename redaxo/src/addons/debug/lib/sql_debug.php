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
    if(!empty(self::$log))
    {
      $tbl = array();
      $tbl[] = array('#','rows','ms','query','file','line');

      foreach(self::$log as $i => $qry)
      {
        // when a extension takes longer than 5ms, send a warning
        $late_notice = strtr($qry['time'], ',', '.') > 5 ? '! SLOW: ' : '' ;

        $tbl[] = array($i+1,$qry['rows'],$late_notice.$qry['time'],$qry['query'],$qry['file'],$qry['line']);
      }

      // INIT FIREPHP
      $options = array(
        'maxObjectDepth'      => rex_config::get('debug','firephp_maxdepth'), // default: 5
        'maxArrayDepth'       => rex_config::get('debug','firephp_maxdepth'), // default: 5
        'maxDepth'            => rex_config::get('debug','firephp_maxdepth'), // default: 10
        'useNativeJsonEncode' => true,                                        // default: true
        'includeLineNumbers'  => true,                                        // default: true
        );
      $firephp = FirePHP::getInstance(true);
      $firephp->setOptions($options);

      $firephp->table('SQL Log ('.count(self::$log).' queries, '.self::$errors.' errors)', $tbl);
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
    self::$log[] = array(
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
