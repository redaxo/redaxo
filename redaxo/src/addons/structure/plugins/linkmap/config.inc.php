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
      $category_id = $params["context"]->getParam('category_id');

      $tree = '';
      $tree .= '<div id="rex-linkmap">'; // TODO adjust id
      $categoryTree = new rex_categoryTree($params["context"]);
			$tree .= $categoryTree->renderTree($category_id);
			
      $articleList = new rex_articleList($params["context"]);
      $tree .= $articleList->renderList($category_id);
      $tree .= '</div>';
      
      $params['subject'] = $tree;

      return $params['subject'];
    });
  }
}