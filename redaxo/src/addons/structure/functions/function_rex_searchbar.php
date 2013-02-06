<?php

/**
 * Backend Search Addon
 *
 * @author markus[dot]staab[at]redaxo[dot]de Markus Staab
 *
 * @package redaxo5
 */

function rex_structure_searchbar()
{
  $message = '';
  $search_result = '';
  $editUrl = 'index.php?page=content&article_id=%s&mode=edit&clang=%s&be_search_article_name=%s';
  $structureUrl = 'index.php?page=structure&category_id=%s&clang=%s&be_search_article_name=%s';

  // ------------ globale Parameter
  $category_id  = rex_request('category_id', 'int');
  $article_id   = rex_request('article_id', 'int');
  $clang        = rex_request('clang', 'int');
  $ctype        = rex_request('ctype', 'int');

  // ------------ Parameter
  $be_search_clang             = rex_request('be_search_clang'       , 'int');
  $be_search_article_name      = rex_request('be_search_article_name', 'string');
  $be_search_article_name_post = rex_post('be_search_article_name', 'string');

  $be_search_article_id = 0;
  if (preg_match('/^[0-9]+$/', $be_search_article_name_post, $matches)) {
    $be_search_article_id = $matches[0];
  }

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

    if (rex_addon::get('structure')->getConfig('searchmode', 'local') != 'global') {
      // Suche auf aktuellen Kontext eingrenzen
      if ($category_id != 0)
        $qry .= ' AND path LIKE "%|' . $category_id . '|%"';
    }

    $search = rex_sql::factory();
//    $search->debugsql = true;
    $search->setQuery($qry);
    $foundRows = $search->getRows();

    // Suche ergab nur einen Treffer => Direkt auf den Treffer weiterleiten
    if ($foundRows == 1) {
      $OOArt = rex_article::getArticleById($search->getValue('id'), $be_search_clang);
      if (rex::getUser()->getComplexPerm('structure')->hasCategoryPerm($OOArt->getCategoryId())) {
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

        if (rex::getUser()->getComplexPerm('structure')->hasCategoryPerm($OOArt->getCategoryId())) {
          if (rex::getUser()->hasPerm('advancedMode[]'))
            $label .= ' [' . $search->getValue('id') . ']';

          $highlightHit = function ($string, $needle) {
            return preg_replace(
              '/(.*)(' . preg_quote($needle, '/') . ')(.*)/i',
              '\\1<span class="be_search-search-hit">\\2</span>\\3',
              $string
            );
          };

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
            $treeLabel = $highlightHit($treeLabel, $needle);

            $s .= '<li>' . $prefix . '<a href="' . sprintf($structureUrl, $treeItem->getId(), $be_search_clang, urlencode($be_search_article_name)) . '">' . $treeLabel . ' </a></li>';
          }

          $prefix = ': ';
          if ($first) {
            $prefix = '';
            $first = false;
          }

          $label = htmlspecialchars($label);
          $label = $highlightHit($label, $needle);

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
  if (rex_be_controller::getCurrentPagePart(1) == 'content') {
    $select_name = 'article_id';
    $add_homepage = false;
    $article_id_input = '
        <input type="hidden" name="article_id" value="' . $article_id . '" />';
  }

  $category_select = new rex_category_select(false, false, true, $add_homepage);
  $category_select->setName($select_name);
  $category_select->setId('rex-id-search-category-id');
  $category_select->setSize('1');
  $category_select->setAttribute('onchange', 'this.form.submit();');
  $category_select->setSelected($category_id);

  $form =
    '<div class="rex-form">
      <form action="' . rex_url::currentBackendPage() . '" method="post">
      <fieldset>
        <input type="hidden" name="category_id" value="' . $category_id . '" />' . $article_id_input . '
        <input type="hidden" name="clang" value="' . $clang . '" />
        <input type="hidden" name="ctype" value="' . $ctype . '" />
        <input type="hidden" name="be_search_clang" value="' . $clang . '" />';



  $formElements = array();

  $n = array();
  $n['label'] = '<label for="rex-id-search-article-name">' . rex_i18n::msg('be_search_article_name') . '</label>';
  $n['field'] = '<input type="text" name="be_search_article_name" id="rex-id-search-article-name" value="' . htmlspecialchars($be_search_article_name) . '" placeholder="' . htmlspecialchars(rex_i18n::msg('be_search_article_name')) . '" />
                 <input class="rex-button" type="submit" name="be_search_start_search" value="' . rex_i18n::msg('be_search_start') . '" />';
  $formElements[] = $n;

  //$formElements = array();
  $n = array();
  $n['label'] = '<label for="rex-id-search-category-id">' . rex_i18n::msg('be_search_quick_navi') . '</label>';
  $n['field'] = $category_select->get();
  $n['after'] = '<noscript><input class="rex-button" type="submit" name="be_search_start_jump" value="' . rex_i18n::msg('be_search_jump_to_category') . '" /></noscript>';
  $formElements[] = $n;

  $fragment = new rex_fragment();
  $fragment->setVar('inline', true);
  $fragment->setVar('group', true);
  $fragment->setVar('elements', $formElements, false);
  $form .= $fragment->parse('core/form/form.tpl');

  $form .= '
        </fieldset>
      </form>
    </div>';


  $fragment = new rex_fragment();
  $fragment->setVar('content', $form . $search_result, false);
  return $message . $fragment->parse('core/toolbar.tpl');

  return $search_bar;
}
