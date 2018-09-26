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

$mypage = 'metainfo';

if (!defined('REX_METAINFO_FIELD_TEXT')) {
    // Feldtypen
    define('REX_METAINFO_FIELD_TEXT', 1);
    define('REX_METAINFO_FIELD_TEXTAREA', 2);
    define('REX_METAINFO_FIELD_SELECT', 3);
    define('REX_METAINFO_FIELD_RADIO', 4);
    define('REX_METAINFO_FIELD_CHECKBOX', 5);
    define('REX_METAINFO_FIELD_REX_MEDIA_WIDGET', 6);
    define('REX_METAINFO_FIELD_REX_MEDIALIST_WIDGET', 7);
    define('REX_METAINFO_FIELD_REX_LINK_WIDGET', 8);
    define('REX_METAINFO_FIELD_REX_LINKLIST_WIDGET', 9);
    define('REX_METAINFO_FIELD_DATE', 10);
    define('REX_METAINFO_FIELD_DATETIME', 11);
    define('REX_METAINFO_FIELD_LEGEND', 12);
    define('REX_METAINFO_FIELD_TIME', 13);
    define('REX_METAINFO_FIELD_COUNT', 13);
}

$this->setProperty('prefixes', ['art_', 'cat_', 'med_', 'clang_']);
$this->setProperty('metaTables', [
    'art_' => rex::getTablePrefix() . 'article',
    'cat_' => rex::getTablePrefix() . 'article',
    'med_' => rex::getTablePrefix() . 'media',
    'clang_' => rex::getTablePrefix() . 'clang',
]);

if (rex::isBackend()) {
    $curDir = __DIR__;
    require_once $curDir . '/functions/function_metainfo.php';

    rex_extension::register('PAGE_CHECKED', 'rex_metainfo_extensions_handler');
}

rex_extension::register('EDITOR_URL', function (rex_extension_point $ep) {
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
