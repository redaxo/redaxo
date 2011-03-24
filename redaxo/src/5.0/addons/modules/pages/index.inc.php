<?php
/**
 *
 * @package redaxo4
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


rex_title($title, array (array ('', rex_i18n::msg('modules')), array ('actions', rex_i18n::msg('actions'))));

require dirname(__FILE__).'/'. $file;