<?php

/**
 *
 * @package redaxo5
 * @version svn:$Id$
 */

$subpage = rex_request('subpage', 'string');
$func = rex_request('func', 'string');
$id = rex_request('id', 'int');

switch($subpage)
{
  case('roles'):
  	$file = 'roles.inc.php';
  	break;
  default:
  	$file = 'users.inc.php';
  	break;
}

rex_title(rex_i18n::msg('user_management'));

require dirname(__FILE__).'/'. $file;