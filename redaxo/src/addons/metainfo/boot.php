<?php

/**
 * MetaForm Addon.
 *
 * @author markus[dot]staab[at]redaxo[dot]de Markus Staab
 *
 * @package redaxo5
 */

$mypage = 'metainfo';
$addon = rex_addon::get('metainfo');

if (!defined('REX_METAINFO_FIELD_TEXT')) {
    // Feldtypen
    /* @deprecated use rex_metainfo_table_manager::FIELD_TEXT instead */
    define('REX_METAINFO_FIELD_TEXT', 1);
    /* @deprecated use rex_metainfo_table_manager::FIELD_TEXTAREA instead */
    define('REX_METAINFO_FIELD_TEXTAREA', 2);
    /* @deprecated use rex_metainfo_table_manager::FIELD_SELECT instead */
    define('REX_METAINFO_FIELD_SELECT', 3);
    /* @deprecated use rex_metainfo_table_manager::FIELD_RADIO instead */
    define('REX_METAINFO_FIELD_RADIO', 4);
    /* @deprecated use rex_metainfo_table_manager::FIELD_CHECKBOX instead */
    define('REX_METAINFO_FIELD_CHECKBOX', 5);
    /* @deprecated use rex_metainfo_table_manager::FIELD_REX_MEDIA_WIDGET instead */
    define('REX_METAINFO_FIELD_REX_MEDIA_WIDGET', 6);
    /* @deprecated use rex_metainfo_table_manager::FIELD_REX_MEDIALIST_WIDGET instead */
    define('REX_METAINFO_FIELD_REX_MEDIALIST_WIDGET', 7);
    /* @deprecated use rex_metainfo_table_manager::FIELD_REX_LINK_WIDGET instead */
    define('REX_METAINFO_FIELD_REX_LINK_WIDGET', 8);
    /* @deprecated use rex_metainfo_table_manager::FIELD_REX_LINKLIST_WIDGET instead */
    define('REX_METAINFO_FIELD_REX_LINKLIST_WIDGET', 9);
    /* @deprecated use rex_metainfo_table_manager::FIELD_DATE instead */
    define('REX_METAINFO_FIELD_DATE', 10);
    /* @deprecated use rex_metainfo_table_manager::FIELD_DATETIME instead */
    define('REX_METAINFO_FIELD_DATETIME', 11);
    /* @deprecated use rex_metainfo_table_manager::FIELD_LEGEND instead */
    define('REX_METAINFO_FIELD_LEGEND', 12);
    /* @deprecated use rex_metainfo_table_manager::FIELD_TIME instead */
    define('REX_METAINFO_FIELD_TIME', 13);
    /* @deprecated use rex_metainfo_table_manager::FIELD_COUNT instead */
    define('REX_METAINFO_FIELD_COUNT', 13);
}

$addon->setProperty('prefixes', ['art_', 'cat_', 'med_', 'clang_']);
$addon->setProperty('metaTables', [
    'art_' => rex::getTablePrefix() . 'article',
    'cat_' => rex::getTablePrefix() . 'article',
    'med_' => rex::getTablePrefix() . 'media',
    'clang_' => rex::getTablePrefix() . 'clang',
]);

if (rex::isBackend()) {
    $curDir = __DIR__;
    require_once $curDir . '/functions/function_metainfo.php';

    rex_view::addCSSFile(rex_url::addonAssets('metainfo', 'metainfo.css'));

    if ('content' == rex_be_controller::getCurrentPagePart(1)) {
        rex_view::addJsFile(rex_url::addonAssets('metainfo', 'metainfo.js'));
    }

    rex_extension::register('PAGE_CHECKED', 'rex_metainfo_extensions_handler');
    rex_extension::register('STRUCTURE_CONTENT_SIDEBAR', function ($ep) {
        $subject = $ep->getSubject();
        $metaSidebar = include rex_addon::get('metainfo')->getPath('pages/content.metainfo.php');
        return $metaSidebar.$subject;
    });
}

rex_extension::register('EDITOR_URL', static function (rex_extension_point $ep) {
    if (!preg_match('@^rex:///metainfo/(\d+)@', $ep->getParam('file'), $match)) {
        return;
    }

    $id = $match[1];
    $sql = rex_sql::factory();
    $sql->setQuery('SELECT `name` FROM '.rex::getTable('metainfo_field').' WHERE id = ? LIMIT 1', [$id]);

    if (!$sql->getRows()) {
        return;
    }

    static $pages = [
        'art_' => 'articles',
        'cat_' => 'categories',
        'med_' => 'media',
        'clang_' => 'clangs',
    ];

    $prefix = rex_metainfo_meta_prefix($sql->getValue('name'));

    return rex_url::backendPage('metainfo/'.$pages[$prefix], ['func' => 'edit', 'field_id' => $id]);
});
