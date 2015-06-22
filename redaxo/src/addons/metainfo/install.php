<?php

/**
 * MetaForm Addon.
 *
 * @author markus[dot]staab[at]redaxo[dot]de Markus Staab
 *
 * @package redaxo5
 *
 * @var rex_addon $this
 */

rex_sql_util::importDump($this->getPath('_install.sql'));

$tablePrefixes = ['article' => ['art_', 'cat_'], 'media' => ['med_']];
$columns = ['article' => [], 'media' => []];
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
];
for ($i = 0; $i < $sql->getRows(); ++$i) {
    $column = $sql->getValue('name');
    if (substr($column, 0, 4) == 'med_') {
        $table = 'media';
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
