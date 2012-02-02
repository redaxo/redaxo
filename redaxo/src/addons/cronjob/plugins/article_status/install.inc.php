<?php

/**
 * Cronjob Addon - Plugin article_status
 *
 * @author gharlan[at]web[dot]de Gregor Harlan
 *
 * @package redaxo5
 */

$sql = rex_sql::factory();
$sql->setQuery('SELECT id FROM '. rex::getTablePrefix() .'cronjob WHERE type="rex_cronjob_article_status" LIMIT 1');
if ($sql->getRows() == 0)
{
  $sql->setTable(rex::getTablePrefix() .'cronjob');
  $sql->setValue('name', 'Artikel-Status');
  $sql->setValue('type', 'rex_cronjob_article_status');
  $sql->setValue('interval', '|1|d|');
  $sql->setValue('environment', '|0|1|');
  $sql->setValue('execution_moment', 1);
  $sql->setValue('status', 0);
  $sql->addGlobalCreateFields();
  $sql->addGlobalUpdateFields();
  $sql->insert();
}

$this->setProperty('install', true);