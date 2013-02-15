<?php

/**
 * Site Structure Addon
 *
 * @author markus[dot]staab[at]redaxo[dot]de Markus Staab
 *
 * @package redaxo5
 */

rex_perm::register('moveArticle[]', null, rex_perm::OPTIONS);
rex_perm::register('moveCategory[]', null, rex_perm::OPTIONS);
rex_perm::register('copyArticle[]', null, rex_perm::OPTIONS);
rex_perm::register('copyContent[]', null, rex_perm::OPTIONS);
rex_perm::register('publishArticle[]', null, rex_perm::OPTIONS);
rex_perm::register('publishCategory[]', null, rex_perm::OPTIONS);
rex_perm::register('article2startpage[]', null, rex_perm::OPTIONS);
rex_perm::register('article2category[]', null, rex_perm::OPTIONS);

rex_complex_perm::register('structure', 'rex_structure_perm');

require_once __DIR__ . '/functions/function_rex_url.php';

if (rex_request('article_id', 'int') == 0)
  rex::setProperty('article_id', rex::getProperty('start_article_id'));
else {
  $article_id = rex_request('article_id', 'int');
  $article_id = rex_article::getArticleById($article_id) instanceof rex_article ? $article_id : rex::getProperty('notfound_article_id');
  rex::setProperty('article_id', $article_id);
}

if (rex::isBackend() && rex::getUser()) {
  $page = new rex_be_page_popup('linkmap', rex_i18n::msg('linkmap'), '');
  $page->setHidden(true);
  $page->setRequiredPermissions('structure/hasStructurePerm');

  $this->setProperty('pages', array(new rex_be_page_main('system', $page)));

  rex_view::addJsFile($this->getAssetsUrl('linkmap.js'));

  rex_extension::register('PAGE_SIDEBAR', function ($params) {
    $category_id = rex_request('category_id', 'int');
    $article_id  = rex_request('article_id',  'int');
    $clang       = rex_request('clang',       'int');
    $ctype       = rex_request('ctype',       'int');

    $category_id = rex_category::getCategoryById($category_id) instanceof rex_category ? $category_id : 0;
    $article_id = rex_article::getArticleById($article_id) instanceof rex_article ? $article_id : 0;
    $clang = rex_clang::exists($clang) ? $clang : rex::getProperty('start_clang_id');

    // TODO - CHECK PERM
    $context = new rex_context(array(
      'page' => 'structure',
      'category_id' => $category_id,
      'article_id' => $article_id,
      'clang' => $clang,
      'ctype' => $ctype,
    ));

    // check if a new category was folded
    $category_id = rex_request('toggle_category_id', 'int', -1);
    $category_id = rex_category::getCategoryById($category_id) instanceof rex_category ? $category_id : -1;

    $tree = '';
    $tree .= '<div id="rex-sitemap">';
    // TODO remove container (just their to get some linkmap styles)
    $tree .= '<div id="rex-linkmap">';
    $categoryTree = new rex_sitemap_category_tree($context);
    $tree .= $categoryTree->getTree($category_id);

    $tree .= '</div>';
    $tree .= '</div>';

    $params['subject'] = $tree;

    return $params['subject'];
  });

  if (rex_be_controller::getCurrentPagePart(1) == 'system') {
    rex_system_setting::register(new rex_system_setting_article_id('start_article_id'));
    rex_system_setting::register(new rex_system_setting_article_id('notfound_article_id'));
  }
}

rex_extension::register('CLANG_ADDED', function ($params) {
  $firstLang = rex_sql::factory();
  $firstLang->setQuery('select * from ' . rex::getTablePrefix() . "article where clang='0'");
  $fields = $firstLang->getFieldnames();

  $newLang = rex_sql::factory();
  // $newLang->setDebug();
  foreach ($firstLang as $firstLangArt) {
    $newLang->setTable(rex::getTablePrefix() . 'article');

    foreach ($fields as $key => $value) {
      if ($value == 'pid')
        echo ''; // nix passiert
      elseif ($value == 'clang')
        $newLang->setValue('clang', $params['clang']->getId());
      elseif ($value == 'status')
        $newLang->setValue('status', '0'); // Alle neuen Artikel offline
      else
        $newLang->setValue($value, $firstLangArt->getValue($value));
    }

    $newLang->insert();
  }
});

rex_extension::register('CLANG_DELETED', function ($params) {
  $del = rex_sql::factory();
  $del->setQuery('delete from ' . rex::getTablePrefix() . "article where clang='" . $params['clang']->getId() . "'");
});
