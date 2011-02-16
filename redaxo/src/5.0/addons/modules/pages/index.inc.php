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
      $title = $REX['I18N']->msg('modules').': '.$REX['I18N']->msg('actions');
      $file = 'module.action.inc.php';
      break;
    }
  default :
    {
      $title = $REX['I18N']->msg('modules');
      $file = 'module.modules.inc.php';
      break;
    }
}


rex_title($title, array (array ('', $REX['I18N']->msg('modules')), array ('actions', $REX['I18N']->msg('actions'))));

require dirname(__FILE__).'/'. $file;