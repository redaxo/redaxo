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
    if ($sql->getRows() != 0) {
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
 */
function rex_metainfo_delete_field_type($field_type_id)
{
    if (!is_int($field_type_id) || empty($field_type_id)) {
        return rex_i18n::msg('minfo_field_error_invalid_typeid');
    }

    $sql = rex_sql::factory();
    $sql->setTable(rex::getTablePrefix() . 'metainfo_type');
    $sql->setWhere(['id' => $field_type_id]);

    $sql->delete();
    return $sql->getRows() == 1;
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

    if ($sql->getRows() != 1) {
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
    if ($sql->getRows() != 0) {
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

    if ($sql->getRows() != 1) {
        return $invalidField;
    }

    $name = $sql->getValue('name');
    $field_id = $sql->getValue('id');

    $prefix = rex_metainfo_meta_prefix($name);
    $metaTable = rex_metainfo_meta_table($prefix);

    // Spalte existiert?
    $sql->setQuery('SELECT * FROM ' . $metaTable . ' LIMIT 1');
    if (!in_array($name, $sql->getFieldnames())) {
        return rex_i18n::msg('minfo_field_error_invalid_name');
    }

    $sql->setTable(rex::getTablePrefix() . 'metainfo_field');
    $sql->setWhere(['id' => $field_id]);

    $sql->delete();

    $tableManager = new rex_metainfo_table_manager($metaTable);
    return $tableManager->deleteColumn($name);
}

/**
 * Extrahiert den Prefix aus dem Namen eine Spalte.
 */
function rex_metainfo_meta_prefix($name)
{
    if (!is_string($name)) {
        return false;
    }

    if (($pos = strpos($name, '_')) !== false) {
        return substr(strtolower($name), 0, $pos + 1);
    }

    return false;
}

/**
 * Gibt die mit dem Prefix verbundenen Tabellennamen zurück.
 */
function rex_metainfo_meta_table($prefix)
{
    $metaTables = rex_addon::get('metainfo')->getProperty('metaTables', []);

    if (isset($metaTables[$prefix])) {
        return $metaTables[$prefix];
    }

    return false;
}

/**
 * Bindet ggf extensions ein.
 *
 * @param rex_extension_point $ep
 */
function rex_metainfo_extensions_handler(rex_extension_point $ep)
{
    $page = $ep->getSubject();
    $mainpage = rex_be_controller::getCurrentPagePart(1);
    $mypage = 'metainfo';

    // additional javascripts
    if ($mainpage == 'metainfo' || $page == 'content/metainfo' || $page == 'structure' || $page == 'system/lang') {
        rex_view::addJsFile(rex_url::addonAssets($mypage, 'metainfo.js'));
    }

    // include extensions
    $curDir = __DIR__ . '/..';
    if ($page == 'structure') {
        require_once $curDir . '/lib/handler/category_handler.php';
    } elseif ($mainpage == 'mediapool') {
        require_once $curDir . '/lib/handler/media_handler.php';
    } elseif ($page == 'system/lang') {
        require_once $curDir . '/lib/handler/clang_handler.php';
    } elseif ($mainpage == 'content') {
        require_once $curDir . '/extensions/extension_content_sidebar.php';
    } elseif ($page == 'backup') {
        require_once $curDir . '/extensions/extension_cleanup.php';
    }
}
