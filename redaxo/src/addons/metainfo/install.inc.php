<?php

/**
 * MetaForm Addon
 *
 * @author markus[dot]staab[at]redaxo[dot]de Markus Staab
 *
 * @package redaxo5
 */

$result = rex_sql_util::importDump($this->getBasePath('_install.sql'));
if ($result !== true) {
  throw new rex_functional_exception($result);
}

$tablePrefixes = array('article' => array('art_', 'cat_'), 'media' => array('med_'));
$columns = array('article' => array(), 'media' => array());
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
$sql->setQuery('SELECT p.name, p.default, t.dbtype, t.dblength FROM ' . rex::getTable('metainfo_params') . ' p, ' . rex::getTable('metainfo_type') . ' t WHERE p.type = t.id');
$rows = $sql->getRows();
$managers = array(
  'article' => new rex_metainfo_table_manager(rex::getTable('article')),
  'media' => new rex_metainfo_table_manager(rex::getTable('media'))
);
for ($i = 0; $i < $sql->getRows(); $i++) {
  $column = $sql->getValue('name');
  if (substr($column, 0, 4) == 'med_')
    $table = 'media';
  else
    $table = 'article';

  if (isset($columns[$table][$column]))
    $managers[$table]->editColumn($column, $column, $sql->getValue('dbtype'), $sql->getValue('dblength'), $sql->getValue('default'));
  else
    $managers[$table]->addColumn($column, $sql->getValue('dbtype'), $sql->getValue('dblength'), $sql->getValue('default'));

  unset($columns[$table][$column]);
  $sql->next();
}
