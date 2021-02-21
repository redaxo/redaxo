<?php

/**
 * MetaForm Addon.
 *
 * @author markus[dot]staab[at]redaxo[dot]de Markus Staab
 *
 * @package redaxo\metainfo
 */

/**
 * Fügt einen neuen Feldtyp ein.
 *
 * Gibt beim Erfolg die Id des Feldes zurück, bei Fehler die Fehlermeldung
 */
function rex_metainfo_add_field_type($label, $dbtype, $dblength)
{
    if (!is_string($label) || empty($label)) {
        return rex_i18n::msg('minfo_field_error_invalid_name');
    }

    if (!is_string($dbtype) || empty($dbtype)) {
        return rex_i18n::msg('minfo_field_error_invalid_type');
    }

    if (!is_int($dblength) || empty($dblength)) {
        return rex_i18n::msg('minfo_field_error_invalid_length');
    }

    $qry = 'SELECT * FROM ' . rex::getTablePrefix() . 'metainfo_type WHERE label=:label LIMIT 1';
    $sql = rex_sql::factory();
    $sql->setQuery($qry, [':label' => $label]);
    if (0 != $sql->getRows()) {
        return rex_i18n::msg('minfo_field_error_unique_type');
    }

    $sql->setTable(rex::getTablePrefix() . 'metainfo_type');
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
 * @return bool|string
 */
function rex_metainfo_delete_field_type($fieldTypeId)
{
    if (!is_int($fieldTypeId) || empty($fieldTypeId)) {
        return rex_i18n::msg('minfo_field_error_invalid_typeid');
    }

    $sql = rex_sql::factory();
    $sql->setTable(rex::getTablePrefix() . 'metainfo_type');
    $sql->setWhere(['id' => $fieldTypeId]);

    $sql->delete();
    return 1 == $sql->getRows();
}

/**
 * Fügt ein MetaFeld hinzu und legt dafür eine Spalte in der MetaTable an.
 */
function rex_metainfo_add_field($title, $name, $priority, $attributes, $type, $default, $params = null, $validate = null, $restrictions = '', $callback = null)
{
    $prefix = rex_metainfo_meta_prefix($name);
    $metaTable = rex_metainfo_meta_table($prefix);

    // Prefix korrekt?
    if (!$metaTable) {
        return rex_i18n::msg('minfo_field_error_invalid_prefix');
    }

    // TypeId korrekt?
    $qry = 'SELECT * FROM ' . rex::getTablePrefix() . 'metainfo_type WHERE id=' . $type . ' LIMIT 2';
    $sql = rex_sql::factory();
    $typeInfos = $sql->getArray($qry);

    if (1 != $sql->getRows()) {
        return rex_i18n::msg('minfo_field_error_invalid_type');
    }

    $fieldDbType = $typeInfos[0]['dbtype'];
    $fieldDbLength = $typeInfos[0]['dblength'];

    // Spalte existiert schon?
    $sql->setQuery('SELECT * FROM ' . $metaTable . ' LIMIT 1');
    if (in_array($name, $sql->getFieldnames())) {
        return rex_i18n::msg('minfo_field_error_unique_name');
    }

    // Spalte extiert laut metainfo_field?
    $qry = 'SELECT * FROM ' . rex::getTablePrefix() . 'metainfo_field WHERE name=:name LIMIT 1';
    $sql = rex_sql::factory();
    $sql->setQuery($qry, [':name' => $name]);
    if (0 != $sql->getRows()) {
        return rex_i18n::msg('minfo_field_error_unique_name');
    }

    $sql->setTable(rex::getTablePrefix() . 'metainfo_field');
    $sql->setValue('title', $title);
    $sql->setValue('name', $name);
    $sql->setValue('priority', $priority);
    $sql->setValue('attributes', $attributes);
    $sql->setValue('type_id', $type);
    $sql->setValue('default', $default);
    $sql->setValue('params', $params);
    $sql->setValue('validate', $validate);
    $sql->setValue('restrictions', $restrictions);
    $sql->setValue('callback', $callback);
    $sql->addGlobalUpdateFields();
    $sql->addGlobalCreateFields();

    $sql->insert();

    // replace LIKE wildcards
    $prefix = str_replace(['_', '%'], ['\_', '\%'], $prefix);

    rex_sql_util::organizePriorities(rex::getTablePrefix() . 'metainfo_field', 'priority', 'name LIKE "' . $prefix . '%"', 'priority, updatedate');

    $tableManager = new rex_metainfo_table_manager($metaTable);
    return $tableManager->addColumn($name, $fieldDbType, $fieldDbLength, $default);
}

function rex_metainfo_delete_field($fieldIdOrName)
{
    // Löschen anhand der FieldId
    if (is_int($fieldIdOrName)) {
        $fieldQry = 'SELECT * FROM ' . rex::getTablePrefix() . 'metainfo_field WHERE id=:idOrName LIMIT 2';
        $invalidField = rex_i18n::msg('minfo_field_error_invalid_fieldid');
    }
    // Löschen anhand des Feldnames
    elseif (is_string($fieldIdOrName)) {
        $fieldQry = 'SELECT * FROM ' . rex::getTablePrefix() . 'metainfo_field WHERE name=:idOrName LIMIT 2';
        $invalidField = rex_i18n::msg('minfo_field_error_invalid_name');
    } else {
        throw new InvalidArgumentException('MetaInfos: Unexpected type for $fieldIdOrName!');
    }
    // Feld existiert?
    $sql = rex_sql::factory();
    $sql->setQuery($fieldQry, [':idOrName' => $fieldIdOrName]);

    if (1 != $sql->getRows()) {
        return $invalidField;
    }

    $name = $sql->getValue('name');
    $fieldId = $sql->getValue('id');

    $prefix = rex_metainfo_meta_prefix($name);
    $metaTable = rex_metainfo_meta_table($prefix);

    // Spalte existiert?
    $sql->setQuery('SELECT * FROM ' . $metaTable . ' LIMIT 1');
    if (!in_array($name, $sql->getFieldnames())) {
        return rex_i18n::msg('minfo_field_error_invalid_name');
    }

    $sql->setTable(rex::getTablePrefix() . 'metainfo_field');
    $sql->setWhere(['id' => $fieldId]);

    $sql->delete();

    $tableManager = new rex_metainfo_table_manager($metaTable);
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
    if (false === $prefix) {
        throw new InvalidArgumentException('$name must be like "prefix_name".');
    }

    return $prefix;
}

/**
 * Gibt die mit dem Prefix verbundenen Tabellennamen zurück.
 */
function rex_metainfo_meta_table(string $prefix)
{
    $metaTables = rex_addon::get('metainfo')->getProperty('metaTables', []);

    if (isset($metaTables[$prefix])) {
        return $metaTables[$prefix];
    }

    return false;
}

/**
 * Bindet ggf extensions ein.
 */
function rex_metainfo_extensions_handler(rex_extension_point $ep)
{
    $page = $ep->getSubject();
    $mainpage = rex_be_controller::getCurrentPagePart(1);
    $mypage = 'metainfo';

    // additional javascripts
    if (in_array($mainpage, ['metainfo', 'mediapool'], true) || in_array($page, ['content/metainfo', 'structure', 'system/lang'], true)) {
        rex_view::addJsFile(rex_url::addonAssets($mypage, 'metainfo.js'), [rex_view::JS_IMMUTABLE => true]);
    }

    // include extensions
    if ('structure' == $page) {
        require_once __DIR__ . '/../lib/handler/category_handler.php';
    } elseif ('mediapool' == $mainpage) {
        require_once __DIR__ . '/../lib/handler/media_handler.php';
    } elseif ('system/lang' == $page) {
        require_once __DIR__ . '/../lib/handler/clang_handler.php';
    } elseif ('content' == $mainpage) {
        require_once __DIR__ . '/../extensions/extension_content_sidebar.php';
    } elseif ('backup' == $page) {
        require_once __DIR__ . '/../extensions/extension_cleanup.php';
    }
}
