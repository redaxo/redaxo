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

if ($REX['REDAXO']){ $I18N->appendFile(dirname(__FILE__) .'/lang'); }

$REX['ADDON']['name'][$mypage] = $I18N->msg('user_management');
$REX['ADDON']['perm'][$mypage] = 'admin[]';
$REX['ADDON']['version'][$mypage] = "1.0";
$REX['ADDON']['author'][$mypage] = "Jan Kristinus";
$REX['ADDON']['supportpage'][$mypage] = 'forum.redaxo.de';
$REX['ADDON']['navigation'][$mypage] = array('block'=>'system');


if($REX['REDAXO'])
{
	rex_register_extension('PAGE_CHECKED', 'rex_subpage_users');
	
	function rex_subpage_users($params)
	{
		global $REX,$I18N;
	
	    $roles = new rex_be_page($I18N->msg('roles'), array('page'=>'users', 'subpage' => ''));
	    $roles->setRequiredPermissions('isAdmin');
	    $roles->setHref('index.php?page=users&subpage=');
	
	    $users = new rex_be_page($I18N->msg('users'), array('page'=>'users', 'subpage' => 'users'));
	    $users->setRequiredPermissions('isAdmin');
	    $users->setHref('index.php?page=users&subpage=users');
	
		$REX['PAGES']['users']->page->addSubPage($roles);
		$REX['PAGES']['users']->page->addSubPage($users);
	
	}

}