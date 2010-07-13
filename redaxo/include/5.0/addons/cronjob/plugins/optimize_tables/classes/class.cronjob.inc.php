<?php

/**
 * Cronjob Addon - Plugin optimize_tables
 *
 * @author gharlan[at]web[dot]de Gregor Harlan
 *
 * @package redaxo4
 * @version svn:$Id$
 */
 
class rex_cronjob_optimize_tables extends rex_cronjob
{
  /*public*/ function execute()
  {
    global $REX;
    $tables = rex_sql::showTables(1, $REX['TABLE_PREFIX']);
    if(is_array($tables) && !empty($tables))
    {
      $sql = rex_sql::factory();
      // $sql->debugsql = true;
      return $sql->setQuery('OPTIMIZE TABLE '. implode(', ', $tables));
    }
    return false;
  }
  
  /*public*/ function getTypeName()
  {
    global $I18N;
    return $I18N->msg('cronjob_optimize_tables');
  }
}