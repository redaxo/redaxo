<?php

/**
 * Site Structure Addon
 *
 * @author markus[dot]staab[at]redaxo[dot]de Markus Staab
 *
 * @package redaxo5
 * @version svn:$Id$
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

rex_var::registerVar('rex_var_link');

//---------------- tree
if (rex::isBackend() && rex::getUser())
{
  rex_perm::register('structure_tree[off]');
  if(!rex::getUser()->hasPerm("structure_tree[off]"))
  {
    rex_extension::register('PAGE_STRUCTURE_HEADER_PRE', function($params){
      // check if a new category was folded
      $category_id = rex_request('toggle_category_id', 'rex-category-id', -1);
      
      $tree = '';
      $tree .= '<div id="rex-linkmap">'; // TODO adjust id
      $categoryTree = new rex_sitemap_categoryTree($params["context"]);
			$tree .= $categoryTree->getTree($category_id);

			// TODO do articles really make sense in the sitemap?
      $articleList = new rex_sitemap_articleList($params["context"]);
      $tree .= $articleList->getList($category_id);
      $tree .= '</div>';
      
      $params['subject'] = $tree;

      return $params['subject'];
    });
  }
}