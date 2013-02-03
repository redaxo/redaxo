<?php

/**
 * Backend Search Addon
 *
 * @author markus[dot]staab[at]redaxo[dot]de Markus Staab
 *
 * @package redaxo5
 */

function rex_be_search_structure($params)
{
  if (!rex::getUser()->hasPerm('be_search[structure]')) {
    return $params['subject'];
  }

  $message = '';
  $search_result = '';
  $editUrl = 'index.php?page=content&article_id=%s&mode=edit&clang=%s&be_search_article_name=%s';
  $structureUrl = 'index.php?page=structure&category_id=%s&clang=%s&be_search_article_name=%s';

  // ------------ globale Parameter
  $mode         = rex_request('mode', 'string');
  $category_id  = rex_request('category_id', 'int');
  $article_id   = rex_request('article_id', 'int');
  $clang        = rex_request('clang', 'int');
  $ctype        = rex_request('ctype', 'int');

  // ------------ Parameter
  $be_search_article_id        = rex_request('be_search_article_id'  , 'int');
  $be_search_clang             = rex_request('be_search_clang'       , 'int');
  $be_search_article_name      = rex_request('be_search_article_name', 'string');
  $be_search_article_name_post = rex_post('be_search_article_name', 'string');

  // ------------ Suche via ArtikelId
  if ($be_search_article_id != 0) {
    $OOArt = rex_article::getArticleById($be_search_article_id, $be_search_clang);
    if ($OOArt instanceof rex_article) {
      header('Location:' . sprintf($editUrl, $be_search_article_id, $be_search_clang, urlencode($be_search_article_name)));
      exit();
    }
  }

  // Auswahl eines normalen Artikels => category holen
  if ($article_id != 0) {
    $OOArt = rex_article::getArticleById($article_id, $clang);
    // Falls Artikel gerade geloescht wird, gibts keinen rex_article
    if ($OOArt)
      $category_id = $OOArt->getCategoryId();
  }

  // ------------ Suche via ArtikelName
  // hier nur den post artikel namen abfragen,
  // da sonst bei vorherigen headerweiterleitungen
  // auch gesucht wuerde
  if ($be_search_article_name_post != '') {
    // replace LIKE wildcards
    $be_search_article_name_like = str_replace(array('_', '%'), array('\_', '\%'), $be_search_article_name);

    $qry = '
    SELECT id
    FROM ' . rex::getTablePrefix() . 'article
    WHERE
      clang = ' . $be_search_clang . ' AND
      (
        name LIKE "%' . $be_search_article_name_like . '%" OR
        catname LIKE "%' . $be_search_article_name_like . '%"
      )';

    switch (rex_addon::get('be_search')->getProperty('searchmode', 'local')) {
      case 'local':
      {
        // Suche auf aktuellen Kontext eingrenzen
        if ($category_id != 0)
          $qry .= ' AND path LIKE "%|' . $category_id . '|%"';
      }
    }

    $search = rex_sql::factory();
//    $search->debugsql = true;
    $search->setQuery($qry);
    $foundRows = $search->getRows();

    // Suche ergab nur einen Treffer => Direkt auf den Treffer weiterleiten
    if ($foundRows == 1) {
      $OOArt = rex_article::getArticleById($search->getValue('id'), $be_search_clang);
      if (rex::getUser()->hasCategoryPerm($OOArt->getCategoryId())) {
        header('Location:' . sprintf($editUrl, $search->getValue('id'), $be_search_clang, urlencode($be_search_article_name)));
        exit();
      }
    }
    // Mehrere Suchtreffer, Liste anzeigen
    elseif ($foundRows > 0) {
      $needle = htmlspecialchars($be_search_article_name);
      $search_result .= '<ul class="be_search-search-result">';
      for ($i = 0; $i < $foundRows; $i++) {
        $OOArt = rex_article::getArticleById($search->getValue('id'), $be_search_clang);
        $label = $OOArt->getName();

        if (rex::getUser()->hasCategoryPerm($OOArt->getCategoryId())) {
          if (rex::getUser()->hasPerm('advancedMode[]'))
            $label .= ' [' . $search->getValue('id') . ']';

          $s = '';
          $first = true;
          foreach ($OOArt->getParentTree() as $treeItem) {
            $treeLabel = $treeItem->getName();

            if (rex::getUser()->hasPerm('advancedMode[]'))
              $treeLabel .= ' [' . $treeItem->getId() . ']';

            $prefix = ': ';
            if ($first) {
              $prefix = '';
              $first = false;
            }

            $treeLabel = htmlspecialchars($treeLabel);
            $treeLabel = rex_be_search_highlight_hit($treeLabel, $needle);

            $s .= '<li>' . $prefix . '<a href="' . sprintf($structureUrl, $treeItem->getId(), $be_search_clang, urlencode($be_search_article_name)) . '">' . $treeLabel . ' </a></li>';
          }

          $prefix = ': ';
          if ($first) {
            $prefix = '';
            $first = false;
          }

          $label = htmlspecialchars($label);
          $label = rex_be_search_highlight_hit($label, $needle);

          $s .= '<li>' . $prefix . '<a href="' . sprintf($editUrl, $search->getValue('id'), $be_search_clang, urlencode($be_search_article_name)) . '">' . $label . ' </a></li>';

          $search_result .= '<li><ul class="be_search-search-hit">' . $s . '</ul></li>';
        }
        $search->next();
      }
      $search_result .= '</ul>';
    } else {
      $message = rex_view::warning(rex_i18n::msg('be_search_no_results'));
    }
  }

  $select_name = 'category_id';
  $add_homepage = true;
  $article_id_input = '';
  if ($mode == 'edit' || $mode == 'meta') {
    $select_name = 'article_id';
    $add_homepage = false;
    $article_id_input = '
        <input type="hidden" name="article_id" value="' . $article_id . '" />';
  }

  $category_select = new rex_category_select(false, false, true, $add_homepage);
  $category_select->setName($select_name);
  $category_select->setId('rex-be_search-category-id');
  $category_select->setSize('1');
  $category_select->setAttribute('onchange', 'this.form.submit();');
  $category_select->setSelected($category_id);

  $form =
   '  <div class="rex-form">
      <form action="' . rex_url::currentBackendPage() . '" method="post">
      <fieldset>
        <input type="hidden" name="mode" value="' . $mode . '" />
        <input type="hidden" name="category_id" value="' . $category_id . '" />' . $article_id_input . '
        <input type="hidden" name="clang" value="' . $clang . '" />
        <input type="hidden" name="ctype" value="' . $ctype . '" />
        <input type="hidden" name="be_search_clang" value="' . $clang . '" />

        <div class="rex-fl-lft">
          <label for="rex-be_search-article-name">' . rex_i18n::msg('be_search_article_name') . '</label>
          <input class="rex-form-text" type="text" name="be_search_article_name" id="rex-be_search-article-name" value="' . htmlspecialchars($be_search_article_name) . '" />

          <label for="rex-be_search-article-id">' . rex_i18n::msg('be_search_article_id') . '</label>
          <input class="rex-form-text" type="text" name="be_search_article_id" id="rex-be_search-article-id" />
          <input class="rex-form-submit" type="submit" name="be_search_start_search" value="' . rex_i18n::msg('be_search_start') . '" />
        </div>

        <div class="rex-fl-rght">
          <label for="rex-be_search-category-id">' . rex_i18n::msg('be_search_quick_navi') . '</label>';

  $form .= $category_select->get() . '
          <noscript>
            <input type="submit" name="be_search_start_jump" value="' . rex_i18n::msg('be_search_jump_to_category') . '" />
          </noscript>
        </div>
        </fieldset>
      </form>
      </div>';

  $search_bar = $message .
  '<div id="rex-be_search-searchbar" class="rex-toolbar rex-toolbar-has-form">
   <div class="rex-toolbar-content">
     ' . $form . '
     ' . $search_result . '
   <div class="rex-clearer"></div>
   </div>
   </div>';

  return $search_bar . $params['subject'];
}
