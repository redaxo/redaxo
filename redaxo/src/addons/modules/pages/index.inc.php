<?php
/**
 *
 * @package redaxo5
 * @version svn:$Id$
 */

// -------------- Defaults

$subpage = rex_request('subpage', 'string');
$func = rex_request('func', 'string');

switch ($subpage)
{
  case 'actions' :
    {
      $title = rex_i18n::msg('modules').': '.rex_i18n::msg('actions');
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

require dirname(__FILE__).'/'. $file;