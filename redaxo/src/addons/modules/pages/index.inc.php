<?php
/**
 *
 * @package redaxo5
 */

// -------------- Defaults

$subpage = rex_request('subpage', 'string');
$func = rex_request('func', 'string');

switch ($subpage) {
  case 'actions' :
    {
      $title = rex_i18n::msg('modules') . ': ' . rex_i18n::msg('actions');
      $file = 'module.action.inc.php';
      break;
    }
  default :
    {
      $title = rex_i18n::msg('modules');
      $file = 'module.modules.inc.php';
      break;
    }
}

echo rex_view::title($title);
$content = rex_file::getOutput(dirname(__FILE__) . '/' . $file);
echo rex_view::contentBlock($content, '', 'block');
