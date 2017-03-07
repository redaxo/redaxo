<?php

if (PHP_VERSION_ID < 50509) {
    throw new rex_functional_exception(rex_i18n::msg('setup_301', PHP_VERSION, '5.5.9'));
}

// Installer >= 2.1.2 required because of https://github.com/redaxo/redaxo/issues/1018
// (Installer < 2.0.3 also works, because it does not contain the bug)
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

if (rex_string::versionCompare(rex::getVersion(), '5.4.0-dev', '<')) {
    $content = <<<'PHP'
#!/usr/bin/env php
<?php

unset($REX);
$REX['REDAXO'] = true;
$REX['HTDOCS_PATH'] = '../';
$REX['BACKEND_FOLDER'] = 'redaxo';

chdir(dirname(__DIR__));

require __DIR__.'/../src/core/console.php';

PHP;

    $path = rex_path::backend('bin/console');
    rex_file::put($path, $content);
    @chmod($path, 0775);

    $content = <<<'HTACCESS'
order deny,allow
deny from all

HTACCESS;

    rex_file::put(rex_path::backend('bin/.htaccess'), $content);
}
