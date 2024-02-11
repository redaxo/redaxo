<?php

rex_sql_table::get(rex::getTable('clang'))
    ->ensurePrimaryIdColumn()
    ->ensureColumn(new rex_sql_column('code', 'varchar(255)'))
    ->ensureColumn(new rex_sql_column('name', 'varchar(255)'))
    ->ensureColumn(new rex_sql_column('priority', 'int(10) unsigned'))
    ->ensureColumn(new rex_sql_column('status', 'tinyint(1)'))
    ->ensureColumn(new rex_sql_column('revision', 'int(10) unsigned'))
    ->ensure();

$sql = rex_sql::factory();
if (!$sql->setQuery('SELECT 1 FROM ' . rex::getTable('clang') . ' LIMIT 1')->getRows()) {
    $sql->setTable(rex::getTable('clang'));
    $sql->setValues(['id' => 1, 'code' => 'de', 'name' => 'deutsch', 'priority' => 1, 'status' => 1, 'revision' => 0]);
    $sql->insert();
}

rex_sql_table::get(rex::getTable('config'))
    ->removeColumn('id')
    ->ensureColumn(new rex_sql_column('namespace', 'varchar(75)'))
    ->ensureColumn(new rex_sql_column('key', 'varchar(255)'))
    ->ensureColumn(new rex_sql_column('value', 'text'))
    ->setPrimaryKey(['namespace', 'key'])
    ->ensure();

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

rex_sql_table::get(rex::getTable('user'))
    ->ensurePrimaryIdColumn()
    ->ensureColumn(new rex_sql_column('name', 'varchar(255)', true))
    ->ensureColumn(new rex_sql_column('description', 'text', true))
    ->ensureColumn(new rex_sql_column('login', 'varchar(50)'))
    ->ensureColumn(new rex_sql_column('password', 'varchar(255)', true))
    ->ensureColumn(new rex_sql_column('email', 'varchar(255)', true))
    ->ensureColumn(new rex_sql_column('status', 'tinyint(1)'))
    ->ensureColumn(new rex_sql_column('admin', 'tinyint(1)'))
    ->ensureColumn(new rex_sql_column('language', 'varchar(255)'))
    ->ensureColumn(new rex_sql_column('startpage', 'varchar(255)'))
    ->ensureColumn(new rex_sql_column('role', 'text', true))
    ->ensureColumn(new rex_sql_column('theme', 'varchar(255)', true))
    ->ensureColumn(new rex_sql_column('login_tries', 'tinyint(4)', false, '0'))
    ->ensureGlobalColumns()
    ->ensureColumn(new rex_sql_column('password_changed', 'datetime'))
    ->ensureColumn(new rex_sql_column('previous_passwords', 'text'))
    ->ensureColumn(new rex_sql_column('password_change_required', 'tinyint(1)'))
    ->ensureColumn(new rex_sql_column('lasttrydate', 'datetime'))
    ->ensureColumn(new rex_sql_column('lastlogin', 'datetime', true))
    ->ensureColumn(new rex_sql_column('session_id', 'varchar(255)', true))
    ->ensureColumn(new rex_sql_column('revision', 'int(10) unsigned'))
    ->ensureIndex(new rex_sql_index('login', ['login'], rex_sql_index::UNIQUE))
    ->removeColumn('cookiekey')
    ->ensure();

rex_sql_table::get(rex::getTable('user_passkey'))
    ->ensureColumn(new rex_sql_column('id', 'varchar(255)'))
    ->ensureColumn(new rex_sql_column('user_id', 'int(10) unsigned'))
    ->ensureColumn(new rex_sql_column('public_key', 'text'))
    ->ensureColumn(new rex_sql_column('createdate', 'datetime'))
    ->setPrimaryKey('id')
    ->ensureForeignKey(new rex_sql_foreign_key(rex::getTable('user_passkey') . '_user_id', rex::getTable('user'), ['user_id' => 'id'], rex_sql_foreign_key::CASCADE, rex_sql_foreign_key::CASCADE))
    ->ensure();

rex_sql_table::get(rex::getTable('user_role'))
    ->ensurePrimaryIdColumn()
    ->ensureColumn(new rex_sql_column('name', 'varchar(255)', true))
    ->ensureColumn(new rex_sql_column('description', 'text', true))
    ->ensureColumn(new rex_sql_column('perms', 'text'))
    ->ensureGlobalColumns()
    ->ensureColumn(new rex_sql_column('revision', 'int(10) unsigned'))
    ->ensure();

rex_sql_table::get(rex::getTable('user_session'))
    ->ensureColumn(new rex_sql_column('session_id', 'varchar(255)'))
    ->ensureColumn(new rex_sql_column('user_id', 'int(10) unsigned'))
    ->ensureColumn(new rex_sql_column('cookie_key', 'varchar(255)', true))
    ->ensureColumn(new rex_sql_column('passkey_id', 'varchar(255)', true))
    ->ensureColumn(new rex_sql_column('ip', 'varchar(39)')) // max for ipv6
    ->ensureColumn(new rex_sql_column('useragent', 'varchar(255)'))
    ->ensureColumn(new rex_sql_column('starttime', 'datetime'))
    ->ensureColumn(new rex_sql_column('last_activity', 'datetime'))
    ->setPrimaryKey('session_id')
    ->ensureIndex(new rex_sql_index('cookie_key', ['cookie_key'], rex_sql_index::UNIQUE))
    ->ensureForeignKey(new rex_sql_foreign_key(rex::getTable('user_session') . '_user_id', rex::getTable('user'), ['user_id' => 'id'], rex_sql_foreign_key::CASCADE, rex_sql_foreign_key::CASCADE))
    ->ensureForeignKey(new rex_sql_foreign_key(rex::getTable('user_session') . '_passkey_id', rex::getTable('user_passkey'), ['passkey_id' => 'id'], rex_sql_foreign_key::CASCADE, rex_sql_foreign_key::CASCADE))
    ->ensure();

$defaultConfig = [
    'phpmailer_from' => '',
    'phpmailer_test_address' => '',
    'phpmailer_fromname' => 'Mailer',
    'phpmailer_confirmto' => '',
    'phpmailer_bcc' => '',
    'phpmailer_mailer' => 'smtp',
    'phpmailer_host' => 'localhost',
    'phpmailer_port' => 587,
    'phpmailer_charset' => 'utf-8',
    'phpmailer_wordwrap' => 120,
    'phpmailer_encoding' => '8bit',
    'phpmailer_priority' => 0,
    'phpmailer_security_mode' => false,
    'phpmailer_smtpsecure' => 'tls',
    'phpmailer_smtpauth' => true,
    'phpmailer_username' => '',
    'phpmailer_password' => '',
    'phpmailer_smtp_debug' => '0',
    'phpmailer_logging' => 0,
    'phpmailer_errormail' => 0,
    'phpmailer_archive' => false,
    'phpmailer_detour_mode' => false,
];

rex_config::refresh();
foreach ($defaultConfig as $key => $value) {
    if (!rex::hasConfig($key)) {
        rex::setConfig($key, $value);
    }
}
