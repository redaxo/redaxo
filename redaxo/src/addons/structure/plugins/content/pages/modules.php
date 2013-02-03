<?php
/**
 *
 * @package redaxo5
 */

// -------------- Defaults

$subpage = rex_be_controller::getCurrentPagePart(2);
$func = rex_request('func', 'string');

switch ($subpage) {
  case 'actions' :
    {
      $title = rex_i18n::msg('modules') . ': ' . rex_i18n::msg('actions');
      $file = 'modules.action.php';
      break;
    }
  default :
    {
      $title = rex_i18n::msg('modules');
      $file = 'modules.modules.php';
      break;
    }
}

echo rex_view::title($title);
require dirname(__FILE__) . '/' . $file;
