<?php

/**
 * Cronjob Addon - Plugin optimize_tables
 *
 * @author gharlan[at]web[dot]de Gregor Harlan
 *
 * @package redaxo5
 */

class rex_cronjob_optimize_tables extends rex_cronjob
{
  public function execute()
  {
    $tables = rex_sql::showTables(1, rex::getTablePrefix());
    if (is_array($tables) && !empty($tables)) {
      $sql = rex_sql::factory();
      // $sql->setDebug();
      return $sql->setQuery('OPTIMIZE TABLE ' . implode(', ', $tables));
    }
    return false;
  }

  public function getTypeName()
  {
    return rex_i18n::msg('cronjob_optimize_tables');
  }
}
