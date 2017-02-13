<?php

if (PHP_VERSION_ID < 50509) {
    throw new rex_functional_exception(rex_i18n::msg('setup_301', PHP_VERSION, '5.5.9'));
}

$installerVersion = rex_addon::get('install')->getVersion();
if (rex_string::versionCompare($installerVersion, '2.1.2-beta2', '<') && rex_string::versionCompare($installerVersion, '2.0.3', '>=')) {
    throw new rex_functional_exception('This update requires at least version <b>2.1.2</b> of the <b>install</b> addon!');
}

if (rex_string::versionCompare(rex::getVersion(), '5.1.0-beta1', '<')) {
    rex_sql_table::get(rex::getTable('user'))
        ->ensureColumn(new rex_sql_column('email', 'varchar(255)', true))
        ->ensureColumn(new rex_sql_column('lastlogin', 'datetime', true))
        ->alter();

    rex_sql_table::get(rex::getTable('clang'))
        ->ensureColumn(new rex_sql_column('status', 'tinyint(1)'))
        ->alter();

    rex_sql::factory()->setQuery('UPDATE '.rex::getTable('clang').' SET `status` = 1');
}

if (rex_string::versionCompare(rex::getVersion(), '5.3.0-beta1', '<')) {
    rex_sql_table::get(rex::getTable('user'))
        ->ensureColumn(new rex_sql_column('role', 'text', true))
        ->alter();
}
