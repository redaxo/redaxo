<?php

/**
 * MetaForm Addon.
 *
 * @author markus[dot]staab[at]redaxo[dot]de Markus Staab
 *
 * @package redaxo5
 */

$addon = rex_addon::get('metainfo');

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

$data = [
    ['id' => 1, 'label' => 'text', 'dbtype' => 'text', 'dblength' => 0],
    ['id' => 2, 'label' => 'textarea', 'dbtype' => 'text', 'dblength' => 0],
    ['id' => 3, 'label' => 'select', 'dbtype' => 'varchar', 'dblength' => 255],
    ['id' => 4, 'label' => 'radio', 'dbtype' => 'varchar', 'dblength' => 255],
    ['id' => 5, 'label' => 'checkbox', 'dbtype' => 'varchar', 'dblength' => 255],
    ['id' => 6, 'label' => 'REX_MEDIA_WIDGET', 'dbtype' => 'varchar', 'dblength' => 255],
    ['id' => 7, 'label' => 'REX_MEDIALIST_WIDGET', 'dbtype' => 'text', 'dblength' => 0],
    ['id' => 8, 'label' => 'REX_LINK_WIDGET', 'dbtype' => 'varchar', 'dblength' => 255],
    ['id' => 9, 'label' => 'REX_LINKLIST_WIDGET', 'dbtype' => 'text', 'dblength' => 0],
    ['id' => 10, 'label' => 'date', 'dbtype' => 'text', 'dblength' => 0],
    ['id' => 11, 'label' => 'datetime', 'dbtype' => 'text', 'dblength' => 0],
    ['id' => 12, 'label' => 'legend', 'dbtype' => 'text', 'dblength' => 0],
    ['id' => 13, 'label' => 'time', 'dbtype' => 'text', 'dblength' => 0],
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
        $prefix = substr($column, 0, 4);
        if (in_array(substr($column, 0, 4), $prefixes)) {
            $columns[$table][$column] = true;
        }
    }
}

$sql = rex_sql::factory();
$sql->setQuery('SELECT p.name, p.default, t.dbtype, t.dblength FROM ' . rex::getTable('metainfo_field') . ' p, ' . rex::getTable('metainfo_type') . ' t WHERE p.type_id = t.id');
$rows = $sql->getRows();
$managers = [
    'article' => new rex_metainfo_table_manager(rex::getTable('article')),
    'media' => new rex_metainfo_table_manager(rex::getTable('media')),
    'clang' => new rex_metainfo_table_manager(rex::getTable('clang')),
];
for ($i = 0; $i < $sql->getRows(); ++$i) {
    $column = $sql->getValue('name');
    if ('med_' == substr($column, 0, 4)) {
        $table = 'media';
    } elseif ('clang_' == substr($column, 0, 6)) {
        $table = 'clang';
    } else {
        $table = 'article';
    }

    if (isset($columns[$table][$column])) {
        $managers[$table]->editColumn($column, $column, $sql->getValue('dbtype'), $sql->getValue('dblength'), $sql->getValue('default'));
    } else {
        $managers[$table]->addColumn($column, $sql->getValue('dbtype'), $sql->getValue('dblength'), $sql->getValue('default'));
    }

    unset($columns[$table][$column]);
    $sql->next();
}
