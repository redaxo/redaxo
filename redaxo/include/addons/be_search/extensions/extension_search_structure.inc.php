<?php

/**
 * Backend Search Addon
 *
 * @author markus[dot]staab[at]redaxo[dot]de Markus Staab
 *
 * @package redaxo4
 * @version svn:$Id$
 */

function rex_a256_search_structure($params)
{
  global $REX, $I18N;

  if(!$REX['USER']->hasPerm('be_search[structure]'))
  {
    return $params['subject'];
  }

  $message = '';
  $search_result = '';
  $editUrl = 'index.php?page=content&article_id=%s&mode=edit&clang=%s&a256_article_name=%s';
  $structureUrl = 'index.php?page=structure&category_id=%s&clang=%s&a256_article_name=%s';

  // ------------ globale Parameter
  $page         = rex_request('page', 'string');
  $mode         = rex_request('mode', 'string');
  $category_id  = rex_request('category_id', 'int');
  $article_id   = rex_request('article_id', 'int');
  $clang        = rex_request('clang', 'int');
  $ctype        = rex_request('ctype', 'int');
  $function     = rex_request('function', 'string');

  // ------------ Parameter
  $a256_article_id        = rex_request('a256_article_id'  , 'int');
  $a256_clang             = rex_request('a256_clang'       , 'int');
  $a256_article_name      = rex_request('a256_article_name', 'string');
  $a256_article_name_post = rex_post('a256_article_name', 'string');
  $mode                   = rex_request('mode', 'string');

  // ------------ Suche via ArtikelId
  if($a256_article_id != 0)
  {
    $OOArt = OOArticle::getArticleById($a256_article_id, $a256_clang);
    if(OOArticle::isValid($OOArt))
    {
      header('Location:'. sprintf($editUrl, $a256_article_id, $a256_clang, urlencode($a256_article_name)));
      exit();
    }
  }

  // Auswahl eines normalen Artikels => category holen
  if($article_id != 0)
  {
    $OOArt = OOArticle::getArticleById($article_id, $clang);
    // Falls Artikel gerade geloescht wird, gibts keinen OOArticle
    if($OOArt)
      $category_id = $OOArt->getCategoryId();
  }

  // ------------ Suche via ArtikelName
  // hier nur den post artikel namen abfragen,
  // da sonst bei vorherigen headerweiterleitungen
  // auch gesucht wuerde
  if($a256_article_name_post != '')
  {
    $qry = '
    SELECT id
    FROM '. $REX['TABLE_PREFIX'] .'article
    WHERE
      clang = '. $a256_clang .' AND
      (
        name LIKE "%'. $a256_article_name .'%" OR
        catname LIKE "%'. $a256_article_name .'%"
      )';

    switch(OOAddon::getProperty('be_search', 'searchmode', 'local'))
    {
      case 'local':
      {
        // Suche auf aktuellen Kontext eingrenzen
        if($category_id != 0)
          $qry .= ' AND path LIKE "%|'. $category_id .'|%"';
      }
    }

    $search = rex_sql::factory();
//    $search->debugsql = true;
    $search->setQuery($qry);
    $foundRows = $search->getRows();

    // Suche ergab nur einen Treffer => Direkt auf den Treffer weiterleiten
    if($foundRows == 1)
    {
      $OOArt = OOArticle::getArticleById($search->getValue('id'), $a256_clang);
      if($REX['USER']->hasCategoryPerm($OOArt->getCategoryId()))
      {
        header('Location:'. sprintf($editUrl, $search->getValue('id'), $a256_clang, urlencode($a256_article_name)));
        exit();
      }
    }
    // Mehrere Suchtreffer, Liste anzeigen
    else if($foundRows > 0)
    {
      $needle = htmlspecialchars($a256_article_name);
      $search_result .= '<ul class="a256-search-result">';
      for($i = 0; $i < $foundRows; $i++)
      {
        $OOArt = OOArticle::getArticleById($search->getValue('id'), $a256_clang);
        $label = $OOArt->getName();

        if($REX['USER']->hasCategoryPerm($OOArt->getCategoryId()))
        {
          if($REX['USER']->hasPerm('advancedMode[]'))
            $label .= ' ['. $search->getValue('id') .']';

          $s = '';
          $first = true;
          foreach($OOArt->getParentTree() as $treeItem)
          {
            $treeLabel = $treeItem->getName();

            if($REX['USER']->hasPerm('advancedMode[]'))
              $treeLabel .= ' ['. $treeItem->getId() .']';

            $prefix = ': ';
            if($first)
            {
              $prefix = '';
              $first = false;
            }

            $treeLabel = htmlspecialchars($treeLabel);
            $treeLabel = rex_a256_highlight_hit($treeLabel, $needle);
          
            $s .= '<li>'. $prefix .'<a href="'. sprintf($structureUrl, $treeItem->getId(), $a256_clang, urlencode($a256_article_name)) .'">'. $treeLabel .' </a></li>';
          }

          $prefix = ': ';
          if($first)
          {
            $prefix = '';
            $first = false;
          }

          $label = htmlspecialchars($label);
          $label = rex_a256_highlight_hit($label, $needle);
          
          $s .= '<li>'. $prefix .'<a href="'. sprintf($editUrl, $search->getValue('id'), $a256_clang, urlencode($a256_article_name)) .'">'. $label .' </a></li>';

          $search_result .= '<li><ul class="a256-search-hit">'. $s .'</ul></li>';
        }
        $search->next();
      }
      $search_result .= '</ul>';
    }
    else
    {
      $message = rex_warning($I18N->msg('be_search_no_results'));
    }
  }

  $select_name = 'category_id';
  $add_homepage = true;
  if($mode == 'edit' || $mode == 'meta')
  {
    $select_name = 'article_id';
    $add_homepage = false;
  }

  $category_select = new rex_category_select(false, false, true, $add_homepage);
  $category_select->setName($select_name);
  $category_select->setId('rex-a256-category-id');
  $category_select->setSize('1');
  $category_select->setAttribute('onchange', 'this.form.submit();');
  $category_select->setSelected($category_id);

  $form =
   '  <div class="rex-form">
      <form action="index.php" method="post">
      <fieldset>
        <input type="hidden" name="page" value="'. $page .'" />
        <input type="hidden" name="mode" value="'. $mode .'" />
        <input type="hidden" name="category_id" value="'. $category_id .'" />
        <input type="hidden" name="article_id" value="'. $article_id .'" />
        <input type="hidden" name="clang" value="'. $clang .'" />
        <input type="hidden" name="ctype" value="'. $ctype .'" />
        <input type="hidden" name="a256_clang" value="'. $clang .'" />

		    <div class="rex-fl-lft">
	        <label for="rex-a256-article-name">'. $I18N->msg('be_search_article_name') .'</label>
    	    <input class="rex-form-text" type="text" name="a256_article_name" id="rex-a256-article-name" value="'. htmlspecialchars(stripslashes($a256_article_name)) .'"'. rex_tabindex() .' />

        	<label for="rex-a256-article-id">'. $I18N->msg('be_search_article_id') .'</label>
	        <input class="rex-form-text" type="text" name="a256_article_id" id="rex-a256-article-id"'. rex_tabindex() .' />
    	    <input class="rex-form-submit" type="submit" name="a256_start_search" value="'. $I18N->msg('be_search_start') .'"'. rex_tabindex() .' />
		    </div>

    		<div class="rex-fl-rght">
    			<label for="rex-a256-category-id">'. $I18N->msg('be_search_quick_navi') .'</label>';

    			$category_select->setAttribute('tabindex', rex_tabindex(false));

  $form .= $category_select->get() .'
    			<noscript>
    			  <input type="submit" name="a256_start_jump" value="'. $I18N->msg('be_search_jump_to_category') .'" />
    			</noscript>
        </div>
        </fieldset>
      </form>
      </div>';

  $search_bar = $message.
  '<div id="rex-a256-searchbar" class="rex-toolbar rex-toolbar-has-form">
   <div class="rex-toolbar-content">
     '. $form .'
     '. $search_result .'
	 <div class="rex-clearer"></div>
   </div>
   </div>';

  return $search_bar . $params['subject'];
}