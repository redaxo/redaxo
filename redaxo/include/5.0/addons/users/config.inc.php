<?php

/**
 * User management
 *
 * @author 
 *
 * @package redaxo5
 * @version svn:$Id$
 */

$mypage = 'users';

if($REX['REDAXO'])
{
  // -------------------- register the addon
  
  $page = new rex_be_page($I18N->msg('user_management'), array('page' => 'users'));
  $page->setRequiredPermissions('isAdmin');

  $users = new rex_be_page($I18N->msg('users'), array('page'=>'users', 'subpage' => ''));
  $users->setRequiredPermissions('isAdmin');
  $users->setHref('index.php?page=users&subpage=');
  
  $roles = new rex_be_page($I18N->msg('roles'), array('page'=>'users', 'subpage' => 'roles'));
  $roles->setRequiredPermissions('isAdmin');
  $roles->setHref('index.php?page=users&subpage=roles');
  
  $page->addSubPage($users);
  $page->addSubPage($roles);
  
  $main_page = new rex_be_page_main('system', $page);
  $main_page->setPrio(50);
  $REX['ADDON']['page'][$mypage] = $main_page;
}