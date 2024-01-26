<?php

rex_sql_table::get(rex::getTable('cronjob'))
    ->ensurePrimaryIdColumn()
    ->ensureColumn(new rex_sql_column('name', 'varchar(255)', true))
    ->ensureColumn(new rex_sql_column('description', 'varchar(255)', true))
    ->ensureColumn(new rex_sql_column('type', 'varchar(255)', true))
    ->ensureColumn(new rex_sql_column('parameters', 'text', true))
    ->ensureColumn(new rex_sql_column('interval', 'text'))
    ->ensureColumn(new rex_sql_column('nexttime', 'datetime', true))
    ->ensureColumn(new rex_sql_column('environment', 'varchar(255)'))
    ->ensureColumn(new rex_sql_column('execution_moment', 'tinyint(1)'))
    ->ensureColumn(new rex_sql_column('execution_start', 'datetime'))
    ->ensureColumn(new rex_sql_column('status', 'tinyint(1)'))
    ->ensureGlobalColumns()
    ->ensure();

/**
 * Cronjob article_status.
 */

$sql = rex_sql::factory();
$sql->setQuery('SELECT id FROM ' . rex::getTablePrefix() . 'cronjob WHERE type="rex_cronjob_article_status" LIMIT 1');
if (0 == $sql->getRows()) {
    $sql->setTable(rex::getTablePrefix() . 'cronjob');
    $sql->setValue('name', 'Artikel-Status');
    $sql->setValue('type', rex_cronjob_article_status::class);
    $sql->setValue('interval', '{"minutes":[0],"hours":[0],"days":"all","weekdays":"all","months":"all"}');
    $sql->setValue('environment', '|frontend|backend|script|');
    $sql->setValue('execution_moment', 1);
    $sql->setValue('status', 0);
    $sql->addGlobalCreateFields();
    $sql->addGlobalUpdateFields();
    $sql->insert();
}

/**
 * Cronjob optimize_tables.
 */

$sql = rex_sql::factory();
$sql->setQuery('SELECT id FROM ' . rex::getTablePrefix() . 'cronjob WHERE type="rex_cronjob_optimize_tables" LIMIT 1');
if (0 == $sql->getRows()) {
    $sql->setTable(rex::getTablePrefix() . 'cronjob');
    $sql->setValue('name', 'Tabellen-Optimierung');
    $sql->setValue('type', rex_cronjob_optimize_tables::class);
    $sql->setValue('interval', '{"minutes":[0],"hours":[0],"days":"all","weekdays":"all","months":"all"}');
    $sql->setValue('environment', '|frontend|backend|script|');
    $sql->setValue('execution_moment', 0);
    $sql->setValue('status', 0);
    $sql->addGlobalCreateFields();
    $sql->addGlobalUpdateFields();
    $sql->insert();
}
