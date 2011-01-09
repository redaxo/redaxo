<?php
/**
 *
 * @package redaxo4
 * @version svn:$Id$
 */

// -------------- Defaults

$subpage = rex_request('subpage', 'string');
$func = rex_request('func', 'string');


switch($subpage)
{
  default : $file = 'content.inc.php'; break;
}

require $REX['INCLUDE_PATH'] . '/core/layout/top.php';
//rex_title($REX['I18N']->msg('specials'),$subline);
require dirname(__FILE__).'/'. $file;
require $REX['INCLUDE_PATH'] . '/core/layout/bottom.php';