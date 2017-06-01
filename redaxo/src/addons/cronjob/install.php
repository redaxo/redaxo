<?php

rex_sql::factory()->setQuery('
    CREATE TABLE IF NOT EXISTS `'.rex::getTable('cronjob').'` (
        id int(10) unsigned NOT NULL AUTO_INCREMENT,
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
');

$table = rex_sql_table::get(rex::getTable('cronjob'));
$table
    ->ensureColumn(new rex_sql_column('id', 'int(10) unsigned', false, null, 'auto_increment'))
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
    ->ensureColumn(new rex_sql_column('createdate', 'datetime'))
    ->ensureColumn(new rex_sql_column('createuser', 'varchar(255)'))
    ->ensureColumn(new rex_sql_column('updatedate', 'datetime'))
    ->ensureColumn(new rex_sql_column('updateuser', 'varchar(255)'))
;
if (method_exists($table, 'ensure')) {
    $table->setPrimaryKey('id')->ensure();
} else {
    $table->alter();
}
