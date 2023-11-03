<?php

/**
 * MetaForm Addon.
 *
 * @author markus[dot]staab[at]redaxo[dot]de Markus Staab
 *
 * @package redaxo\metainfo
 */

rex_extension::register('BACKUP_BEFORE_DB_IMPORT', 'rex_metainfo_cleanup');

/**
 * Alle Metafelder löschen, nicht das nach einem Import in der Parameter Tabelle
 * noch Datensätze zu Feldern stehen, welche nicht als Spalten in der
 * rex_article angelegt wurden!
 * @param rex_extension_point|array $epOrParams
 * @return void
 */
function rex_metainfo_cleanup($epOrParams)
{
    $params = $epOrParams instanceof rex_extension_point ? $epOrParams->getParams() : $epOrParams;
    // Cleanup nur durchführen, wenn auch die rex_article Tabelle neu angelegt wird
    if (isset($params['force']) && true != $params['force'] &&
        !str_contains($params['content'], 'CREATE TABLE `' . rex::getTablePrefix() . 'article`') &&
        !str_contains($params['content'], 'CREATE TABLE ' . rex::getTablePrefix() . 'article')
    ) {
        return;
    }

    // check wheter tables exists
    $tables = rex_sql::factory()->getTables();
    if (!isset($tables[rex::getTablePrefix() . 'metainfo_field'])) {
        return;
    }

    // since this extension may be used also when the addon is not yet installed,
    // require needed classes manually
    require_once __DIR__ . '/../lib/table_manager.php';

    $sql = rex_sql::factory();
    $sql->setQuery('SELECT name FROM ' . rex::getTablePrefix() . 'metainfo_field');

    for ($i = 0; $i < $sql->getRows(); ++$i) {
        $prefix = rex_metainfo_meta_prefix((string) $sql->getValue('name'));
        $table = rex_type::string(rex_metainfo_meta_table($prefix));
        $tableManager = new rex_metainfo_table_manager($table);

        $tableManager->deleteColumn((string) $sql->getValue('name'));

        $sql->next();
    }

    // evtl reste aufräumen
    $tablePrefixes = ['article' => ['art_', 'cat_'], 'media' => ['med_'], 'clang' => ['clang_']];
    foreach ($tablePrefixes as $table => $prefixes) {
        $table = rex::getTablePrefix() . $table;
        $tableManager = new rex_metainfo_table_manager($table);

        foreach (rex_sql::showColumns($table) as $column) {
            $column = $column['name'];
            if (in_array(substr($column, 0, 4), $prefixes)) {
                $tableManager->deleteColumn($column);
            }
        }
    }

    $sql = rex_sql::factory();
    $sql->setQuery('DELETE FROM ' . rex::getTablePrefix() . 'metainfo_field');
}
