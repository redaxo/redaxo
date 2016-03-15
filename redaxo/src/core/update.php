<?php

if (rex_string::versionCompare(rex::getVersion(), '5.1.0', '<')) {
    rex_sql_table::get(rex::getTable('user'))
        ->ensureColumn(new rex_sql_column('email', 'varchar(255)', true))
        ->ensureColumn(new rex_sql_column('lastlogin', 'datetime', true))
        ->alter();

    rex_sql_table::get(rex::getTable('clang'))
        ->ensureColumn(new rex_sql_column('status', 'tinyint(1)'))
        ->alter();

    rex_sql::factory()->setQuery('UPDATE '.rex::getTable('clang').' SET `status` = 1');
}
