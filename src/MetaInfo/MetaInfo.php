<?php

use Redaxo\Core\Backend\Controller;
use Redaxo\Core\Core;
use Redaxo\Core\Database\Sql;
use Redaxo\Core\Database\Table;
use Redaxo\Core\Database\Util;
use Redaxo\Core\ExtensionPoint\ExtensionPoint;
use Redaxo\Core\Filesystem\Url;
use Redaxo\Core\MetaInfo\Database\Table as MetaInfoTable;
use Redaxo\Core\Translation\I18n;
use Redaxo\Core\Util\Type;
use Redaxo\Core\View\Asset;

/**
 * Fügt einen neuen Feldtyp ein.
 *
 * Gibt beim Erfolg die Id des Feldes zurück, bei Fehler die Fehlermeldung
 *
 * @param string $label
 * @param string $dbtype
 * @param int $dblength
 *
 * @return int|string
 */
function rex_metainfo_add_field_type($label, $dbtype, $dblength)
{
    if (!is_string($label) || empty($label)) {
        return I18n::msg('minfo_field_error_invalid_name');
    }

    if (!is_string($dbtype) || empty($dbtype)) {
        return I18n::msg('minfo_field_error_invalid_type');
    }

    if (!is_int($dblength) || empty($dblength)) {
        return I18n::msg('minfo_field_error_invalid_length');
    }

    $qry = 'SELECT * FROM ' . Core::getTablePrefix() . 'metainfo_type WHERE label=:label LIMIT 1';
    $sql = Sql::factory();
    $sql->setQuery($qry, [':label' => $label]);
    if (0 != $sql->getRows()) {
        return I18n::msg('minfo_field_error_unique_type');
    }

    $sql->setTable(Core::getTablePrefix() . 'metainfo_type');
    $sql->setValue('label', $label);
    $sql->setValue('dbtype', $dbtype);
    $sql->setValue('dblength', $dblength);

    $sql->insert();
    return $sql->getLastId();
}

/**
 * Löscht einen Feldtyp.
 *
 * Gibt beim Erfolg true zurück, sonst eine Fehlermeldung
 *
 * @param int $fieldTypeId
 *
 * @return bool|string
 */
function rex_metainfo_delete_field_type($fieldTypeId)
{
    if (!is_int($fieldTypeId) || empty($fieldTypeId)) {
        return I18n::msg('minfo_field_error_invalid_typeid');
    }

    $sql = Sql::factory();
    $sql->setTable(Core::getTablePrefix() . 'metainfo_type');
    $sql->setWhere(['id' => $fieldTypeId]);

    $sql->delete();
    return 1 == $sql->getRows();
}

/**
 * Fügt ein MetaFeld hinzu und legt dafür eine Spalte in der MetaTable an.
 *
 * @param string $title
 * @param string $name
 * @param int $priority
 * @param string $attributes
 * @param int $type
 * @param string $default
 * @param string $params
 * @param string $validate
 * @param string $restrictions
 *
 * @return bool|string
 */
function rex_metainfo_add_field($title, $name, $priority, $attributes, $type, $default, $params = null, $validate = null, $restrictions = '')
{
    $prefix = rex_metainfo_meta_prefix($name);
    $metaTable = rex_metainfo_meta_table($prefix);

    // Prefix korrekt?
    if (!$metaTable) {
        return I18n::msg('minfo_field_error_invalid_prefix');
    }

    // TypeId korrekt?
    $qry = 'SELECT * FROM ' . Core::getTablePrefix() . 'metainfo_type WHERE id=' . $type . ' LIMIT 2';
    $sql = Sql::factory();
    $typeInfos = $sql->getArray($qry);

    if (1 != $sql->getRows()) {
        return I18n::msg('minfo_field_error_invalid_type');
    }

    $fieldDbType = (string) $typeInfos[0]['dbtype'];
    $fieldDbLength = (int) $typeInfos[0]['dblength'];

    // Spalte existiert schon?
    $sql->setQuery('SELECT * FROM ' . $metaTable . ' LIMIT 1');
    if (in_array($name, $sql->getFieldnames())) {
        return I18n::msg('minfo_field_error_unique_name');
    }

    // Spalte extiert laut metainfo_field?
    $qry = 'SELECT * FROM ' . Core::getTablePrefix() . 'metainfo_field WHERE name=:name LIMIT 1';
    $sql = Sql::factory();
    $sql->setQuery($qry, [':name' => $name]);
    if (0 != $sql->getRows()) {
        return I18n::msg('minfo_field_error_unique_name');
    }

    $sql->setTable(Core::getTablePrefix() . 'metainfo_field');
    $sql->setValue('title', $title);
    $sql->setValue('name', $name);
    $sql->setValue('priority', $priority);
    $sql->setValue('attributes', $attributes);
    $sql->setValue('type_id', $type);
    $sql->setValue('default', $default);
    $sql->setValue('params', $params);
    $sql->setValue('validate', $validate);
    $sql->setValue('restrictions', $restrictions);
    $sql->addGlobalUpdateFields();
    $sql->addGlobalCreateFields();

    $sql->insert();

    // replace LIKE wildcards
    $prefix = $sql->escape($sql->escapeLikeWildcards($prefix) . '%');

    Util::organizePriorities(Core::getTablePrefix() . 'metainfo_field', 'priority', 'name LIKE ' . $prefix, 'priority, updatedate');

    $tableManager = new MetaInfoTable($metaTable);
    return $tableManager->addColumn($name, $fieldDbType, $fieldDbLength, $default);
}

/**
 * @param string|int $fieldIdOrName
 *
 * @return bool|string
 */
function rex_metainfo_delete_field($fieldIdOrName)
{
    // Löschen anhand der FieldId
    if (is_int($fieldIdOrName)) {
        $fieldQry = 'SELECT * FROM ' . Core::getTablePrefix() . 'metainfo_field WHERE id=:idOrName LIMIT 2';
        $invalidField = I18n::msg('minfo_field_error_invalid_fieldid');
    }
    // Löschen anhand des Feldnames
    elseif (is_string($fieldIdOrName)) {
        $fieldQry = 'SELECT * FROM ' . Core::getTablePrefix() . 'metainfo_field WHERE name=:idOrName LIMIT 2';
        $invalidField = I18n::msg('minfo_field_error_invalid_name');
    } else {
        throw new InvalidArgumentException('MetaInfos: Unexpected type for $fieldIdOrName!');
    }
    // Feld existiert?
    $sql = Sql::factory();
    $sql->setQuery($fieldQry, [':idOrName' => $fieldIdOrName]);

    if (1 != $sql->getRows()) {
        return $invalidField;
    }

    $name = (string) $sql->getValue('name');
    $fieldId = $sql->getValue('id');

    $prefix = rex_metainfo_meta_prefix($name);
    $metaTable = Type::string(rex_metainfo_meta_table($prefix));

    // Spalte existiert?
    $sql->setQuery('SELECT * FROM ' . $metaTable . ' LIMIT 1');
    if (!in_array($name, $sql->getFieldnames())) {
        return I18n::msg('minfo_field_error_invalid_name');
    }

    $sql->setTable(Core::getTablePrefix() . 'metainfo_field');
    $sql->setWhere(['id' => $fieldId]);

    $sql->delete();

    $tableManager = new MetaInfoTable($metaTable);
    return $tableManager->deleteColumn($name);
}

/**
 * Extrahiert den Prefix aus dem Namen eine Spalte.
 *
 * @return string
 */
function rex_metainfo_meta_prefix(string $name)
{
    if (false === ($pos = strpos($name, '_'))) {
        throw new InvalidArgumentException('$name must be like "prefix_name"');
    }

    $prefix = substr(strtolower($name), 0, $pos + 1);
    if ('' === $prefix) {
        throw new InvalidArgumentException('$name must be like "prefix_name".');
    }

    return $prefix;
}

/**
 * Gibt die mit dem Prefix verbundenen Tabellennamen zurück.
 * @return string|false
 */
function rex_metainfo_meta_table(string $prefix)
{
    $metaTables = Core::getProperty('metainfo_metaTables', []);

    return $metaTables[$prefix] ?? false;
}

/**
 * Bindet ggf extensions ein.
 * @return void
 */
function rex_metainfo_extensions_handler(ExtensionPoint $ep)
{
    $page = $ep->getSubject();
    $mainpage = Controller::getCurrentPagePart(1);

    // additional javascripts
    if (in_array($mainpage, ['metainfo', 'mediapool'], true) || in_array($page, ['content/metainfo', 'structure', 'system/lang'], true)) {
        Asset::addJsFile(Url::coreAssets('js/metainfo.js'), [Asset::JS_IMMUTABLE => true]);
    }

    // include extensions
    if ('structure' == $page) {
        require_once dirname(__DIR__, 4) . '/src/MetaInfo/Handler/CategoryHandler.php';
    } elseif ('mediapool' == $mainpage) {
        require_once dirname(__DIR__, 4) . '/src/MetaInfo/Handler/MediaHandler.php';
    } elseif ('system/lang' == $page) {
        require_once dirname(__DIR__, 4) . '/src/MetaInfo/Handler/LanguageHandler.php';
    } elseif ('backup' == $page) {
        require_once __DIR__ . '/function_metainfo_extension_cleanup.php';
    }
}

/**
 * Alle Metafelder löschen, nicht das nach einem Import in der Parameter Tabelle
 * noch Datensätze zu Feldern stehen, welche nicht als Spalten in der
 * rex_article angelegt wurden!
 * @param ExtensionPoint|array $epOrParams
 * @return void
 */
function rex_metainfo_cleanup($epOrParams)
{
    $params = $epOrParams instanceof ExtensionPoint ? $epOrParams->getParams() : $epOrParams;
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
