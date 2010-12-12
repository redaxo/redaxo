<?php

/**
 * Page Content Modules Addon
 *
 * @author markus[dot]staab[at]redaxo[dot]de Markus Staab
 *
 * @package redaxo4
 * @version svn:$Id$
 */

$mypage = 'modules';

if ($REX['REDAXO'])
{
  // -------------------- register the addon
  
  $page = new rex_be_page($I18N->msg('modules'), array('page' => 'modules'));
  $page->setRequiredPermissions('isAdmin');
  
  $modules = new rex_be_page($I18N->msg('modules'), array('page'=>'modules', 'subpage' => ''));
  $modules->setRequiredPermissions('isAdmin');
  $modules->setHref('index.php?page=modules&subpage=');

  $actions = new rex_be_page($I18N->msg('actions'), array('page'=>'modules', 'subpage' => 'actions'));
  $actions->setRequiredPermissions('isAdmin');
  $actions->setHref('index.php?page=modules&subpage=actions');

  $page->addSubPage($modules);
  $page->addSubPage($actions);
    
  $REX['ADDON']['page'][$mypage] = new rex_be_page_main('system', $page);
}
