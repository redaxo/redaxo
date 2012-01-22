<?php

/**
 * MetaForm Addon
 * @author markus[dot]staab[at]redaxo[dot]de Markus Staab
 *
 * @package redaxo5
 * @version svn:$Id$
 */

rex_extension::register('A1_BEFORE_DB_IMPORT', 'rex_metainfo_cleanup');

/**
 * Alle Metafelder löschen, nicht das nach einem Import in der Parameter Tabelle
 * noch Datensätze zu Feldern stehen, welche nicht als Spalten in der
 * rex_article angelegt wurden!
 */
function rex_metainfo_cleanup($params)
{
	// Cleanup nur durchführen, wenn auch die rex_article Tabelle neu angelegt wird
	if(isset($params['force']) && $params['force'] != true &&
     strpos($params['content'], 'CREATE TABLE `'. rex::getTablePrefix() .'article`') === false &&
	   strpos($params['content'], 'CREATE TABLE '. rex::getTablePrefix() .'article') === false)
  {
    return;
  }

  // check wheter tables exists
  $tables = rex_sql::showTables();
  if(!isset($tables[rex::getTablePrefix() . 'metainfo_params']))
  {
    return false;
  }

  // since this extension may be used also when the addon is not yet installed,
  // require needed classes manually
  require_once dirname(__FILE__) .'/../lib/table_manager.php';

  $sql = rex_sql::factory();
  $sql->setQuery('SELECT name FROM ' . rex::getTablePrefix() . 'metainfo_params');

  for ($i = 0; $i < $sql->getRows(); $i++)
  {
    if (substr($sql->getValue('name'), 0, 4) == 'med_')
      $tableManager = new rex_metainfo_tableManager(rex::getTablePrefix() . 'media');
    else
      $tableManager = new rex_metainfo_tableManager(rex::getTablePrefix() . 'article');

    $tableManager->deleteColumn($sql->getValue('name'));

    $sql->next();
  }


  // evtl reste aufräumen
  $tablePrefixes = array('article' => array('art_', 'cat_'), 'media' => array('med_'));
  foreach($tablePrefixes as $table => $prefixes)
  {
    $table = rex::getTablePrefix() .$table;
    $tableManager = new rex_metainfo_tableManager($table);

    foreach(rex_sql::showColumns($table) as $column)
    {
      $column = $column['name'];
      if(in_array(substr($column, 0, 4), $prefixes))
      {
        $tableManager->deleteColumn($column);
      }
    }
  }

  $sql = rex_sql::factory();
  $sql->setQuery('DELETE FROM '. rex::getTablePrefix() .'metainfo_params');
}