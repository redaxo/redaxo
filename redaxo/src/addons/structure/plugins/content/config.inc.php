<?php

/**
 * Page Content Addon
 *
 * @author markus[dot]staab[at]redaxo[dot]de Markus Staab
 *
 * @package redaxo5
 * @version svn:$Id$
 */

rex_perm::register('moveSlice[]', null, rex_perm::OPTIONS);

rex_var::registerVar('rex_var_value');

if (rex::isBackend())
{
  $page = new rex_be_page(rex_i18n::msg('content'), array('page' => 'linkmap'));
  $page->setRequiredPermissions('structure/hasStructurePerm');
  $page->setHidden(true);
  $this->setProperty('page', new rex_be_page_main('system', $page));

  rex_extension::register('CLANG_DELETED',
    function($params)
    {
      $del = rex_sql::factory();
      $del->setQuery("delete from ". rex::getTablePrefix() ."article_slice where clang='". $params['id'] ."'");
    }
  );
}
else
{
  rex_extension::register('FE_OUTPUT',
    function($params)
    {
      $content = $params['subject'];

      $article = new rex_article;
      $article->setCLang(rex_clang::getId());

      if ($article->setArticleId(rex::getProperty('article_id')))
      {
         $content .= $article->getArticleTemplate();
      }
      else
      {
        $content .= 'Kein Startartikel selektiert / No starting Article selected. Please click here to enter <a href="redaxo/index.php">redaxo</a>';
      }

      $art_id = $article->getArticleId();
      if($art_id == rex::getProperty('notfound_article_id') && $art_id != rex::getProperty('start_article_id'))
      {
        header("HTTP/1.0 404 Not Found");
      }

      // ----- inhalt ausgeben
      rex_response::sendArticle($content, $article->getValue('updatedate'), $article->getValue('pid'));
    }
  );
}