<?php

/**
 * Site Structure Addon.
 *
 * @author markus[dot]staab[at]redaxo[dot]de Markus Staab
 *
 * @package redaxo5
 */

$addon = rex_addon::get('structure');

rex_perm::register('moveArticle[]', null, rex_perm::OPTIONS);
rex_perm::register('moveCategory[]', null, rex_perm::OPTIONS);
rex_perm::register('copyArticle[]', null, rex_perm::OPTIONS);
rex_perm::register('copyContent[]', null, rex_perm::OPTIONS);
rex_perm::register('publishArticle[]', null, rex_perm::OPTIONS);
rex_perm::register('publishCategory[]', null, rex_perm::OPTIONS);
rex_perm::register('article2startarticle[]', null, rex_perm::OPTIONS);
rex_perm::register('article2category[]', null, rex_perm::OPTIONS);

rex_complex_perm::register('structure', 'rex_structure_perm');

require_once __DIR__ . '/functions/function_rex_url.php';

$addon->setProperty('start_article_id', $addon->getConfig('start_article_id', 1));
$addon->setProperty('notfound_article_id', $addon->getConfig('notfound_article_id', 1));

if (rex_request('article_id', 'int') == 0) {
    $addon->setProperty('article_id', rex_article::getSiteStartArticleId());
} else {
    $article_id = rex_request('article_id', 'int');
    $article_id = rex_article::get($article_id) ? $article_id : rex_article::getNotfoundArticleId();
    $addon->setProperty('article_id', $article_id);
}

if (rex::isBackend() && rex::getUser()) {
    rex_view::addJsFile($addon->getAssetsUrl('linkmap.js'));

    if (rex_be_controller::getCurrentPagePart(1) == 'system') {
        rex_system_setting::register(new rex_system_setting_article_id('start_article_id'));
        rex_system_setting::register(new rex_system_setting_article_id('notfound_article_id'));
    }
}

rex_extension::register('CLANG_ADDED', function (rex_extension_point $ep) {
    $firstLang = rex_sql::factory();
    $firstLang->setQuery('select * from ' . rex::getTablePrefix() . 'article where clang_id=?', [rex_clang::getStartId()]);
    $fields = $firstLang->getFieldnames();

    $newLang = rex_sql::factory();
    // $newLang->setDebug();
    foreach ($firstLang as $firstLangArt) {
        $newLang->setTable(rex::getTablePrefix() . 'article');

        foreach ($fields as $key => $value) {
            if ($value == 'pid') {
                echo '';
            } // nix passiert
            elseif ($value == 'clang_id') {
                $newLang->setValue('clang_id', $ep->getParam('clang')->getId());
            } elseif ($value == 'status') {
                $newLang->setValue('status', '0');
            } // Alle neuen Artikel offline
            else {
                $newLang->setValue($value, $firstLangArt->getValue($value));
            }
        }

        $newLang->insert();
    }
});

rex_extension::register('CLANG_DELETED', function (rex_extension_point $ep) {
    $del = rex_sql::factory();
    $del->setQuery('delete from ' . rex::getTablePrefix() . 'article where clang_id=?', [$ep->getParam('clang')->getId()]);
});

rex_extension::register('CACHE_DELETED', function () {
    rex_structure_element::clearInstancePool();
    rex_structure_element::clearInstanceListPool();
    rex_structure_element::resetClassVars();
});
