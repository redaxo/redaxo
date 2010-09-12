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
  
  $roles = new rex_be_page($I18N->msg('roles'), array('page'=>'users', 'subpage' => ''));
  $roles->setRequiredPermissions('isAdmin');
  $roles->setHref('index.php?page=users&subpage=');

  $users = new rex_be_page($I18N->msg('users'), array('page'=>'users', 'subpage' => 'users'));
  $users->setRequiredPermissions('isAdmin');
  $users->setHref('index.php?page=users&subpage=users');
    
  $page->addSubPage($roles);
  $page->addSubPage($users);
  
  $REX['ADDON']['page'][$mypage] = new rex_be_page_main('system', $page);
  $REX['ADDON']['version'][$mypage] = "1.0";
  $REX['ADDON']['author'][$mypage] = "Jan Kristinus";
  $REX['ADDON']['supportpage'][$mypage] = 'forum.redaxo.de';
}