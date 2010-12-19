<?php

/**
 *
 * @package redaxo4
 * @version svn:$Id$
 */

$subpage = rex_request('subpage', 'string');
$func = rex_request('func', 'string');

// -------------- Header
$subline = array(
  array( '', $REX['I18N']->msg('users')),
  array( 'roles', $REX['I18N']->msg('roles')),
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

require $REX['INCLUDE_PATH'] . '/core/layout/top.php';

rex_title($REX['I18N']->msg('user_management'),$subline);

require dirname(__FILE__).'/'. $file;
require $REX['INCLUDE_PATH'] . '/core/layout/bottom.php';