<?php

/**
 *
 * @package redaxo5
 * @version svn:$Id$
 */

$subpage = rex_request('subpage', 'string');
$func = rex_request('func', 'string');
$id = rex_request('id', 'int');

if($subpage == 'roles' && rex::getUser()->isAdmin())
  $file = 'roles.inc.php';
else
  $file = 'users.inc.php';

rex_title(rex_i18n::msg('user_management'));

require dirname(__FILE__).'/'. $file;