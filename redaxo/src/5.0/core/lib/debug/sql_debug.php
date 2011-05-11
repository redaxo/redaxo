<?php

rex_extension::register('OUTPUT_FILTER', array('rex_sql_debug', 'printStats'));

class rex_sql_debug extends rex_sql
{
  private static 
    $count = 0, 
    $queries = array();
    
  public function __construct($DBID = 1)
  {
    parent::__construct($DBID);
  }    
  
  public function execute(array $params)
  {
    self::$count++;
    $qry = $this->stmt->queryString;
    
    $start = microtime(true);
    parent::execute($params);
    $stop = microtime(true);
    
    $diff = ($stop - $start)*1000;
    self::$queries[] = array($qry, rex_formatter::format($diff, 'number', array(3)));
  }
  
  static public function printStats($params)
  {
    $debugout = '';
    
    foreach(self::$queries as $qry)
    {
      $debugout .= $qry[0]. ' ' .$qry[1] . 'ms<br/>';
    }
    
    return str_replace('<div id="sidebar">', '<div>'. $debugout .'</div><div id="sidebar">', $params['subject']);
  }
}