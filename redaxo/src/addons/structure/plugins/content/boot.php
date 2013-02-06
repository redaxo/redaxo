<?php

/**
 * Page Content Addon
 *
 * @author markus[dot]staab[at]redaxo[dot]de Markus Staab
 *
 * @package redaxo5
 */

rex_perm::register('moveSlice[]', null, rex_perm::OPTIONS);
rex_complex_perm::register('modules', 'rex_module_perm');

if (rex::isBackend()) {
  $pages = array();

  $page = new rex_be_page('content', rex_i18n::msg('content'));
  $page->setRequiredPermissions('structure/hasStructurePerm');
  $page->setHidden(true);
  $page->setPath($this->getPath('pages/content.php'));
  $subpage = new rex_be_page('edit', rex_i18n::msg('edit_mode'));
  $page->addSubPage($subpage);
  $subpage = new rex_be_page('meta', rex_i18n::msg('metadata'));
  $page->addSubPage($subpage);
  $subpage = new rex_be_page('functions', rex_i18n::msg('metafuncs'));
  $page->addSubPage($subpage);
  $pages[] = new rex_be_page_main('system', $page);

  $page = new rex_be_page('templates', rex_i18n::msg('templates'));
  $page->setRequiredPermissions('admin');
  $page->setPath($this->getPath('pages/templates.php'));
  $mainPage = new rex_be_page_main('system', $page);
  $mainPage->setPrio(30);
  $pages[] = $mainPage;

  $page = new rex_be_page('modules', rex_i18n::msg('modules'));
  $page->setRequiredPermissions('admin');
  $page->setPath($this->getPath('pages/modules.php'));
  $page->addSubPage(new rex_be_page('modules', rex_i18n::msg('modules')));
  $page->addSubPage(new rex_be_page('actions', rex_i18n::msg('actions')));
  $mainPage = new rex_be_page_main('system', $page);
  $mainPage->setPrio(40);
  $pages[] = $mainPage;

  $this->setProperty('pages', $pages);

  if (rex_be_controller::getCurrentPagePart(1) == 'system') {
    rex_system_setting::register(new rex_system_setting_default_template_id());
  }

  rex_extension::register('CLANG_DELETED', function ($params) {
    $del = rex_sql::factory();
    $del->setQuery('delete from ' . rex::getTablePrefix() . "article_slice where clang='" . $params['clang']->getId() . "'");
  });
} else {
  rex_extension::register('FE_OUTPUT', function ($params) {
    $content = $params['subject'];

    $article = new rex_article_content;
    $article->setCLang(rex_clang::getCurrentId());

    if ($article->setArticleId(rex::getProperty('article_id'))) {
      if (rex_request::isPJAXRequest()) {
        $content .= $article->getArticle();
      } else {
        $content .= $article->getArticleTemplate();
      }
    } else {
      $content .= 'Kein Startartikel selektiert / No starting Article selected. Please click here to enter <a href="' . rex_url::backendController() . '">redaxo</a>';
      rex_response::sendArticle($content);
      exit;
    }

    $art_id = $article->getArticleId();
    if ($art_id == rex::getProperty('notfound_article_id') && $art_id != rex::getProperty('start_article_id')) {
      rex_response::setStatus(rex_response::HTTP_NOT_FOUND);
    }

    // ----- inhalt ausgeben
    rex_response::sendArticle($content, $article->getValue('updatedate'), $article->getValue('pid'));
  });
}
