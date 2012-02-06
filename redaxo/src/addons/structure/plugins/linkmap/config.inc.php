<?php

/**
 * Site Structure Addon
 *
 * @author markus[dot]staab[at]redaxo[dot]de Markus Staab
 *
 * @package redaxo5
 */

$mypage = 'linkmap';

//---------------- linkmap
if (rex::isBackend())
{
  $page = new rex_be_page_popup(rex_i18n::msg('linkmap'), '', array('page' => 'linkmap'));
  $page->setHidden(true);
  $page->setRequiredPermissions('structure/hasStructurePerm');

  $this->setProperty('page', new rex_be_page_main('system', $page));

  if(rex::getUser())
  {
    rex_extension::register('PAGE_HEADER', function($params){
      $params['subject'] .= "\n  ".
        '<script type="text/javascript" src="'. rex_path::pluginAssets('structure', 'linkmap', 'linkmap.js') .'"></script>';

      return $params['subject'];
    });
  }
}

//---------------- tree
if (rex::isBackend() && rex::getUser())
{
  rex_perm::register('structure_tree[off]');
  if(!rex::getUser()->hasPerm("structure_tree[off]"))
  {
    rex_extension::register('PAGE_SIDEBAR', function($params){

	   	$category_id = rex_request('category_id', 'rex-category-id');
		$article_id  = rex_request('article_id',  'rex-article-id');
		$clang       = rex_request('clang',       'rex-clang-id');
		$ctype       = rex_request('ctype',       'rex-ctype-id');

		// TODO - CHECK PERM

    	$context = new rex_context(array(
		  'page' => 'structure',
		  'category_id' => $category_id,
		  'article_id' => $article_id,
		  'clang' => $clang,
		  'ctype' => $ctype,
		));

      // check if a new category was folded
      $category_id = rex_request('toggle_category_id', 'rex-category-id', -1);

      $tree = '';
      $tree .= '<div id="rex-sitemap">';
      // TODO remove container (just their to get some linkmap styles)
      $tree .= '<div id="rex-linkmap">';
      $categoryTree = new rex_sitemap_categoryTree($context);
			$tree .= $categoryTree->getTree($category_id);

      $tree .= '</div>';
      $tree .= '</div>';

      $params['subject'] = $tree;

      return $params['subject'];
    });
  }
}