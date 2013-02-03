<?php

/**
 *
 * @package redaxo5
 */

$subpage = rex_be_controller::getCurrentPagePart(2);
$func = rex_request('func', 'string');
$id = rex_request('id', 'int');

if ($subpage == 'roles' && rex::getUser()->isAdmin())
  $file = 'roles.php';
else
  $file = 'users.php';

echo rex_view::title(rex_i18n::msg('user_management'));

require dirname(__FILE__) . '/' . $file;
