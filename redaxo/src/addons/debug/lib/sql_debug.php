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
  private static $count   = 0;
  private static $tbl     = array();

  public function setQuery($qry, array $params = array())
  {
    try {
      $ret = parent::setQuery($qry, $params);
    } catch (rex_exception $e) {
      $trace = debug_backtrace();
      for( $i=0 ; $trace && $i<sizeof($trace) ; $i++ ) {
          if (isset($trace[$i]['file']) && strpos($trace[$i]['file'], 'sql.php') === false) {
              $file = $trace[$i]['file'];
              $line = $trace[$i]['line'];
              break;
          }
      }
      $firephp = FirePHP::getInstance(true);
      $firephp->error($e->getMessage() .' in ' . $file . ' on line '. $line);
      throw $e; // re-throw exception after logging
    }
    return $ret;
  }

  public function execute(array $params = array())
  {
    $qry   = $this->stmt->queryString;

    $timer = new rex_timer();
    $res   = parent::execute($params);

    $err = $errno = '';
    if($this->hasError())
    {
      self::$errors++;
      $err   = parent::getError();
      $errno = parent::getErrno();
    }

    self::$queries[] = array(
      'rows'  =>$this->getRows(),
      'time'  =>$timer->getFormattedTime(rex_timer::MILLISEC),
      'query' =>$qry,
      'error' =>$err,
      'errno' =>$errno
      );
    self::$count++;

    return $res;
  }

  static public function doLog($params)
  {
    if(!empty(self::$queries))
    {
      $firephp = FirePHP::getInstance(true);
      self::$tbl[] = array('#','rows','ms','query');
      $i = 0;

      foreach(self::$queries as $qry)
      {
        // when a extension takes longer than 5ms, send a warning
        if(strtr($qry['time'], ',', '.') > 5)
        {
          self::$tbl[] = array($i,$qry['rows'],'! SLOW: '.$qry['time'],$qry['query']);
        }
        else
        {
          self::$tbl[] = array($i,$qry['rows'],$qry['time'],$qry['query']);
        }
        $i++;
      }

      $firephp->table(__CLASS__.' ('.self::$count.' queries, '.self::$errors.' errors)',self::$tbl);
    }
  }
}
