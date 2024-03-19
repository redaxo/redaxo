<?php

use Redaxo\Core\Core;
use Redaxo\Core\Database\Sql;
use Redaxo\Core\Database\Table;
use Redaxo\Core\MetaInfo\Database\Table as MetaInfoTable;
use Redaxo\Core\Util\Type;

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
    if (
        isset($params['force']) && true != $params['force']
        && !str_contains($params['content'], 'CREATE TABLE `' . Core::getTablePrefix() . 'article`')
        && !str_contains($params['content'], 'CREATE TABLE ' . Core::getTablePrefix() . 'article')
    ) {
        return;
    }

    if (!Table::get(Core::getTable('metainfo_field'))->exists()) {
        return;
    }

    $sql = Sql::factory();
    $sql->setQuery('SELECT name FROM ' . Core::getTablePrefix() . 'metainfo_field');

    for ($i = 0; $i < $sql->getRows(); ++$i) {
        $prefix = rex_metainfo_meta_prefix((string) $sql->getValue('name'));
        $table = Type::string(rex_metainfo_meta_table($prefix));
        $tableManager = new MetaInfoTable($table);

        $tableManager->deleteColumn((string) $sql->getValue('name'));

        $sql->next();
    }

    // evtl reste aufräumen
    $tablePrefixes = ['article' => ['art_', 'cat_'], 'media' => ['med_'], 'clang' => ['clang_']];
    foreach ($tablePrefixes as $table => $prefixes) {
        $table = Core::getTablePrefix() . $table;
        $tableManager = new MetaInfoTable($table);

        foreach (Sql::showColumns($table) as $column) {
            $column = $column['name'];
            if (in_array(substr($column, 0, 4), $prefixes)) {
                $tableManager->deleteColumn($column);
            }
        }
    }

    $sql = Sql::factory();
    $sql->setQuery('DELETE FROM ' . Core::getTablePrefix() . 'metainfo_field');
}
