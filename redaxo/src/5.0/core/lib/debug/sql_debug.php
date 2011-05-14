<?php

rex_extension::register('OUTPUT_FILTER', array('rex_sql_debug', 'printStats'));

class rex_sql_debug extends rex_sql
{
  private static
    $count = 0,
    $queries = array();

  public function execute(array $params)
  {
    self::$count++;
    $qry = $this->stmt->queryString;

    $timer = new rex_timer();
    $res = parent::execute($params);

    self::$queries[] = array($qry, $timer->stop(3, 1000));
    
    return $res;
  }

  static public function printStats($params)
  {
    $debugout = '';

    foreach(self::$queries as $qry)
    {
      $debugout .= 'Query: '. $qry[0]. ' ' .$qry[1] . 'ms<br/>';
    }
    
    return rex_debug_util::injectHtml($debugout, $params['subject']);
  }
}