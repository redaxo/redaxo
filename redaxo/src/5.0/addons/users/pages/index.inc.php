<?php

/**
 *
 * @package redaxo5
 * @version svn:$Id$
 */

$subpage = rex_request('subpage', 'string');
$func = rex_request('func', 'string');

// -------------- Header
$subline = array(
  array( '', rex_i18n::msg('users')),
  array( 'roles', rex_i18n::msg('roles')),
);

switch($subpage)
{
  case('roles'):
  	$file = 'roles.inc.php';
  	break;
  default:
  	$file = 'users.inc.php';
  	break;
}

rex_title(rex_i18n::msg('user_management'),$subline);

require dirname(__FILE__).'/'. $file;