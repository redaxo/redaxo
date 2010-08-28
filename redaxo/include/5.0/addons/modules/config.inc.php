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

// $REX['ADDON']['rxid'][$mypage] = '62';
$REX['ADDON']['name'][$mypage] = $I18N->msg('modules');
$REX['ADDON']['perm'][$mypage] = 'isAdmin';
$REX['ADDON']['version'][$mypage] = "1.3";
$REX['ADDON']['author'][$mypage] = "Markus Staab";
$REX['ADDON']['supportpage'][$mypage] = 'forum.redaxo.de';
$REX['ADDON']['navigation'][$mypage] = array('block'=>'system');

if ($REX['REDAXO'])
{
  $I18N->appendFile(dirname(__FILE__) .'/lang');
}

//    $modules = new rex_be_page($I18N->msg('modules'), array('page'=>'module', 'subpage' => ''));
//    $modules->setIsCorePage(true);
//    $modules->setRequiredPermissions('isAdmin');
//    $modules->setHref('index.php?page=module&subpage=');
//    
//    $actions = new rex_be_page($I18N->msg('actions'), array('page'=>'module', 'subpage' => 'actions'));
//    $actions->setIsCorePage(true);
//    $actions->setRequiredPermissions('isAdmin');
//    $actions->setHref('index.php?page=module&subpage=actions');
//    
//    $mainModules = new rex_be_page($I18N->msg('modules'), array('page'=>'module'));
//    $mainModules->setIsCorePage(true);
//    $mainModules->setRequiredPermissions('isAdmin');
//    $mainModules->addSubPage($modules);
//    $mainModules->addSubPage($actions);
//    $pages['module'] = new rex_be_page_main('system', $mainModules);
