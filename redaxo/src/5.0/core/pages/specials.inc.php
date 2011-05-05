<?php
/**
 *
 * @package redaxo5
 * @version svn:$Id$
 */

// -------------- Defaults

$subpage = rex_request('subpage', 'string');
$func = rex_request('func', 'string');

// -------------- Header
$subline = array(
  array( '', rex_i18n::msg('main_preferences')),
  array( 'lang', rex_i18n::msg('languages')),
  array( 'log', rex_i18n::msg('syslog')),
);

rex_title(rex_i18n::msg('specials'),$subline);

switch($subpage)
{
  case 'lang': $file = 'specials.clangs.inc.php'; break;
  case 'log': $file = 'specials.log.inc.php'; break;
  default : $file = 'specials.settings.inc.php'; break;
}

require rex_path::core('pages/'.$file);