<?php
/**
 *
 * @package redaxo4
 * @version svn:$Id$
 */

// -------------- Defaults

$subpage = rex_request('subpage', 'string');
$func = rex_request('func', 'string');

// -------------- Header

$subline = array(
  array( '', $I18N->msg('main_preferences')),
  array( 'lang', $I18N->msg('languages')),
  array( 'log', $I18N->msg('syslog')),
);

rex_title($I18N->msg('specials'),$subline);

switch($subpage)
{
  case 'lang': $file = 'specials.clangs.inc.php'; break;
  case 'log': $file = 'specials.log.inc.php'; break;
  default : $file = 'specials.settings.inc.php'; break;
}

require $REX['SRC_PATH'] .'/core/pages/'.$file;