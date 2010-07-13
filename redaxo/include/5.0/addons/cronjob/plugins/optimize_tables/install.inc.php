<?php

/**
 * Cronjob Addon - Plugin optimize_tables
 *
 * @author gharlan[at]web[dot]de Gregor Harlan
 *
 * @package redaxo4
 * @version svn:$Id$
 */

$sql = rex_sql::factory();
$sql->setQuery('SELECT id FROM '. $REX['TABLE_PREFIX'] .'630_cronjobs WHERE type="rex_cronjob_optimize_tables" LIMIT 1');
if ($sql->getRows() == 0)
{
  $sql->setTable($REX['TABLE_PREFIX'] .'630_cronjobs');
  $sql->setValue('name', 'Tabellen-Optimierung');
  $sql->setValue('type', 'rex_cronjob_optimize_tables');
  $sql->setValue('interval', '|1|d|');
  $sql->setValue('environment', '|0|1|');
  $sql->setValue('status', 0);
  $sql->addGlobalCreateFields();
  $sql->addGlobalUpdateFields();
  $sql->insert();
}

$REX['ADDON']['install']['optimize_tables'] = 1;