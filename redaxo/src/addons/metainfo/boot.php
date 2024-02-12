<?php

/**
 * MetaForm Addon.
 *
 * @author markus[dot]staab[at]redaxo[dot]de Markus Staab
 */

$addon = rex_addon::get('metainfo');

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

    if ('content' == rex_be_controller::getCurrentPagePart(1)) {
        rex_view::addCSSFile(rex_url::addonAssets('metainfo', 'metainfo.css'));
        rex_view::addJsFile(rex_url::addonAssets('metainfo', 'metainfo.js'));
    }

    rex_extension::register('PAGE_CHECKED', 'rex_metainfo_extensions_handler');
    rex_extension::register('STRUCTURE_CONTENT_SIDEBAR', function ($ep) {
        $subject = $ep->getSubject();
        $metaSidebar = include rex_addon::get('metainfo')->getPath('pages/content.metainfo.php');
        return $metaSidebar . $subject;
    });
}

rex_extension::register('EDITOR_URL', static function (rex_extension_point $ep) {
    if (!preg_match('@^rex:///metainfo/(\d+)@', $ep->getParam('file'), $match)) {
        return null;
    }

    $id = $match[1];
    $sql = rex_sql::factory();
    $sql->setQuery('SELECT `name` FROM ' . rex::getTable('metainfo_field') . ' WHERE id = ? LIMIT 1', [$id]);

    if (!$sql->getRows()) {
        return null;
    }

    $prefix = rex_metainfo_meta_prefix((string) $sql->getValue('name'));
    $page = match ($prefix) {
        'art_' => 'articles',
        'cat_' => 'categories',
        'med_' => 'media',
        'clang_' => 'clangs',
        default => throw new LogicException('Unknown metainfo prefix "' . $prefix . '"'),
    };

    return rex_url::backendPage('metainfo/' . $page, ['func' => 'edit', 'field_id' => $id]);
});
