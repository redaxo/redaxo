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

rex_sql_table::get(rex::getTable('metainfo_type'))
    ->ensurePrimaryIdColumn()
    ->ensureColumn(new rex_sql_column('label', 'varchar(255)', true))
    ->ensureColumn(new rex_sql_column('dbtype', 'varchar(255)'))
    ->ensureColumn(new rex_sql_column('dblength', 'int(11)'))
    ->ensure();

rex_sql_table::get(rex::getTable('metainfo_field'))
    ->ensurePrimaryIdColumn()
    ->ensureColumn(new rex_sql_column('title', 'varchar(255)', true))
    ->ensureColumn(new rex_sql_column('name', 'varchar(255)', true))
    ->ensureColumn(new rex_sql_column('priority', 'int(10) unsigned'))
    ->ensureColumn(new rex_sql_column('attributes', 'text'))
    ->ensureColumn(new rex_sql_column('type_id', 'int(10) unsigned', true))
    ->ensureColumn(new rex_sql_column('default', 'varchar(255)'))
    ->ensureColumn(new rex_sql_column('params', 'text', true))
    ->ensureColumn(new rex_sql_column('validate', 'text', true))
    ->ensureColumn(new rex_sql_column('callback', 'text', true))
    ->ensureColumn(new rex_sql_column('restrictions', 'text', true))
    ->ensureColumn(new rex_sql_column('templates', 'text', true))
    ->ensureGlobalColumns()
    ->ensureIndex(new rex_sql_index('name', ['name'], rex_sql_index::UNIQUE))
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
    'be_style_compile' => false,
    'be_style_labelcolor' => '#3bb594',
    'be_style_showlink' => true,
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

$data = [
    ['id' => rex_metainfo_default_type::TEXT, 'label' => 'text', 'dbtype' => 'text', 'dblength' => 0],
    ['id' => rex_metainfo_default_type::TEXTAREA, 'label' => 'textarea', 'dbtype' => 'text', 'dblength' => 0],
    ['id' => rex_metainfo_default_type::SELECT, 'label' => 'select', 'dbtype' => 'varchar', 'dblength' => 255],
    ['id' => rex_metainfo_default_type::RADIO, 'label' => 'radio', 'dbtype' => 'varchar', 'dblength' => 255],
    ['id' => rex_metainfo_default_type::CHECKBOX, 'label' => 'checkbox', 'dbtype' => 'varchar', 'dblength' => 255],
    [
        'id' => rex_metainfo_default_type::REX_MEDIA_WIDGET,
        'label' => 'REX_MEDIA_WIDGET',
        'dbtype' => 'varchar',
        'dblength' => 255
    ],
    [
        'id' => rex_metainfo_default_type::REX_MEDIALIST_WIDGET,
        'label' => 'REX_MEDIALIST_WIDGET',
        'dbtype' => 'text',
        'dblength' => 0
    ],
    [
        'id' => rex_metainfo_default_type::REX_LINK_WIDGET,
        'label' => 'REX_LINK_WIDGET',
        'dbtype' => 'varchar',
        'dblength' => 255
    ],
    [
        'id' => rex_metainfo_default_type::REX_LINKLIST_WIDGET,
        'label' => 'REX_LINKLIST_WIDGET',
        'dbtype' => 'text',
        'dblength' => 0
    ],
    ['id' => rex_metainfo_default_type::DATE, 'label' => 'date', 'dbtype' => 'text', 'dblength' => 0],
    ['id' => rex_metainfo_default_type::DATETIME, 'label' => 'datetime', 'dbtype' => 'text', 'dblength' => 0],
    ['id' => rex_metainfo_default_type::LEGEND, 'label' => 'legend', 'dbtype' => 'text', 'dblength' => 0],
    ['id' => rex_metainfo_default_type::TIME, 'label' => 'time', 'dbtype' => 'text', 'dblength' => 0],
    // XXX neue konstanten koennen hier nicht verwendet werden, da die updates mit der vorherigen version der klasse ausgefuehrt werden
];

$sql = rex_sql::factory();
$sql->setTable(rex::getTable('metainfo_type'));
foreach ($data as $row) {
    $sql->addRecord(static function (rex_sql $record) use ($row) {
        $record->setValues($row);
    });
}
$sql->insertOrUpdate();

$tablePrefixes = ['article' => ['art_', 'cat_'], 'media' => ['med_'], 'clang' => ['clang_']];
$columns = ['article' => [], 'media' => [], 'clang' => []];
foreach ($tablePrefixes as $table => $prefixes) {
    foreach (rex_sql::showColumns(rex::getTable($table)) as $column) {
        $column = $column['name'];
        if (in_array(substr($column, 0, 4), $prefixes)) {
            $columns[$table][$column] = true;
        }
    }
}

$sql = rex_sql::factory();
$sql->setQuery('SELECT p.name, p.default, t.dbtype, t.dblength FROM '.rex::getTable('metainfo_field').' p, '.rex::getTable('metainfo_type').' t WHERE p.type_id = t.id');
$managers = [
    'article' => new rex_metainfo_table_manager(rex::getTable('article')),
    'media' => new rex_metainfo_table_manager(rex::getTable('media')),
    'clang' => new rex_metainfo_table_manager(rex::getTable('clang')),
];
for ($i = 0; $i < $sql->getRows(); ++$i) {
    $column = (string) $sql->getValue('name');
    if (str_starts_with($column, 'med_')) {
        $table = 'media';
    } elseif (str_starts_with($column, 'clang_')) {
        $table = 'clang';
    } else {
        $table = 'article';
    }

    $default = $sql->getValue('default');
    $default = null === $default ? $default : (string) $default;

    if (isset($columns[$table][$column])) {
        $managers[$table]->editColumn($column, $column, (string) $sql->getValue('dbtype'), (int) $sql->getValue('dblength'), $default);
    } else {
        $managers[$table]->addColumn($column, (string) $sql->getValue('dbtype'), (int) $sql->getValue('dblength'), $default);
    }

    unset($columns[$table][$column]);
    $sql->next();
}
