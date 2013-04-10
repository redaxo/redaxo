<?php

/**
 * Site Structure Addon
 *
 * @author markus[dot]staab[at]redaxo[dot]de Markus Staab
 *
 * @package redaxo5
 *
 * @var rex_addon $this
 */

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

if (rex_request('article_id', 'int') == 0) {
    rex::setProperty('article_id', rex::getProperty('start_article_id'));
} else {
    $article_id = rex_request('article_id', 'int');
    $article_id = rex_article::get($article_id) ? $article_id : rex::getProperty('notfound_article_id');
    rex::setProperty('article_id', $article_id);
}

if (rex::isBackend() && rex::getUser()) {
    rex_view::addJsFile($this->getAssetsUrl('linkmap.js'));

    rex_extension::register('PAGE_SIDEBAR', function () {
        $category_id = rex_request('category_id', 'int');
        $article_id  = rex_request('article_id',  'int');
        $clang       = rex_request('clang',       'int');
        $ctype       = rex_request('ctype',       'int');

        $category_id = rex_category::get($category_id) ? $category_id : 0;
        $article_id = rex_article::get($article_id) ? $article_id : 0;
        $clang = rex_clang::exists($clang) ? $clang : rex::getProperty('start_clang_id');

        // TODO - CHECK PERM
        $context = new rex_context([
            'page' => 'structure',
            'category_id' => $category_id,
            'article_id' => $article_id,
            'clang' => $clang,
            'ctype' => $ctype,
        ]);

        // check if a new category was folded
        $category_id = rex_request('toggle_category_id', 'int', -1);
        $category_id = rex_category::get($category_id) ? $category_id : -1;

        $tree = '';
        $tree .= '<div id="rex-sitemap">';
        // TODO remove container (just their to get some linkmap styles)
        $tree .= '<div id="rex-linkmap">';
        $categoryTree = new rex_sitemap_category_tree($context);
        $tree .= $categoryTree->getTree($category_id);

        $tree .= '</div>';
        $tree .= '</div>';

        return $tree;
    });

    if (rex_be_controller::getCurrentPagePart(1) == 'system') {
        rex_system_setting::register(new rex_system_setting_article_id('start_article_id'));
        rex_system_setting::register(new rex_system_setting_article_id('notfound_article_id'));
    }
}

rex_extension::register('CLANG_ADDED', function (rex_extension_point $ep) {
    $firstLang = rex_sql::factory();
    $firstLang->setQuery('select * from ' . rex::getTablePrefix() . 'article where clang=?', [rex::getProperty('start_clang_id')]);
    $fields = $firstLang->getFieldnames();

    $newLang = rex_sql::factory();
    // $newLang->setDebug();
    foreach ($firstLang as $firstLangArt) {
        $newLang->setTable(rex::getTablePrefix() . 'article');

        foreach ($fields as $key => $value) {
            if ($value == 'pid') {
                echo '';
            } // nix passiert
            elseif ($value == 'clang') {
                $newLang->setValue('clang', $ep->getParam('clang')->getId());
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
    $del->setQuery('delete from ' . rex::getTablePrefix() . "article where clang='" . $ep->getParam('clang')->getId() . "'");
});
