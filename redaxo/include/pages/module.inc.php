<?php

$subpage = rex_request('subpage', 'string');

switch ($subpage)
{
  case 'actions' :
    {
      $title = $I18N->msg('modules').': '.$I18N->msg('actions');
      $file = 'module.action.inc.php';
      break;
    }
  default :
    {
      $title = $I18N->msg('modules');
      $file = 'module.modules.inc.php';
      break;
    }
}

rex_title($title, array (array ('', $I18N->msg('modules')), array ('actions', $I18N->msg('actions'))));

require $REX['INCLUDE_PATH'].'/pages/'.$file;