<?php

/**
 * MetaForm Addon.
 *
 * @author markus[dot]staab[at]redaxo[dot]de Markus Staab
 */

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

if (!class_exists(rex_metainfo_default_type::class)) {
    require_once __DIR__ . '/lib/default_type.php';
}

$data = [
    ['id' => rex_metainfo_default_type::TEXT, 'label' => 'text', 'dbtype' => 'text', 'dblength' => 0],
    ['id' => rex_metainfo_default_type::TEXTAREA, 'label' => 'textarea', 'dbtype' => 'text', 'dblength' => 0],
    ['id' => rex_metainfo_default_type::SELECT, 'label' => 'select', 'dbtype' => 'varchar', 'dblength' => 255],
    ['id' => rex_metainfo_default_type::RADIO, 'label' => 'radio', 'dbtype' => 'varchar', 'dblength' => 255],
    ['id' => rex_metainfo_default_type::CHECKBOX, 'label' => 'checkbox', 'dbtype' => 'varchar', 'dblength' => 255],
    ['id' => rex_metainfo_default_type::REX_MEDIA_WIDGET, 'label' => 'REX_MEDIA_WIDGET', 'dbtype' => 'varchar', 'dblength' => 255],
    ['id' => rex_metainfo_default_type::REX_MEDIALIST_WIDGET, 'label' => 'REX_MEDIALIST_WIDGET', 'dbtype' => 'text', 'dblength' => 0],
    ['id' => rex_metainfo_default_type::REX_LINK_WIDGET, 'label' => 'REX_LINK_WIDGET', 'dbtype' => 'varchar', 'dblength' => 255],
    ['id' => rex_metainfo_default_type::REX_LINKLIST_WIDGET, 'label' => 'REX_LINKLIST_WIDGET', 'dbtype' => 'text', 'dblength' => 0],
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
        $record
            ->setValues($row);
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
$sql->setQuery('SELECT p.name, p.default, t.dbtype, t.dblength FROM ' . rex::getTable('metainfo_field') . ' p, ' . rex::getTable('metainfo_type') . ' t WHERE p.type_id = t.id');
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
