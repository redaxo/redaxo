<?php

rex_extension::register('OUTPUT_FILTER', array('rex_sql_debug', 'doLog'));

/**
 * Class to monitor sql queries
 *
 * @author staabm
 */
class rex_sql_debug extends rex_sql
{
  private static
    $queries = array();

  public function execute($params)
  {
    $qry = $this->stmt->queryString;

    $timer = new rex_timer();
    $res = parent::execute($params);

    self::$queries[] = array($qry, $timer->getFormattedTime(rex_timer::MILLISEC));

    return $res;
  }

  static public function doLog($params)
  {
    if(!empty(self::$queries))
    {
      $firephp = FirePHP::getInstance(true);
      $firephp->group(__CLASS__);
      foreach(self::$queries as $qry)
      {
        $firephp->log('Query: '. $qry[0]. ' ' .$qry[1] . 'ms');
      }
      $firephp->groupEnd();
    }
  }
}