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
  array( '', $I18N->msg('roles')),
  array( 'users', $I18N->msg('users')),
);

switch($subpage)
{
  case('users'):
  	$file = 'users.inc.php';
  	break;
  default:
  	$file = 'roles.inc.php';
  	break;
}

require $REX['SRC_PATH'] . '/core/layout/top.php';

rex_title($I18N->msg('title_user'),$subline);

require dirname(__FILE__).'/'. $file;
require $REX['SRC_PATH'] . '/core/layout/bottom.php';