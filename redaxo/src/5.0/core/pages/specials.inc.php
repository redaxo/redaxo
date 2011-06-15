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
if($subpage != 'phpinfo')
{
  rex_title(rex_i18n::msg('specials'));
}

switch($subpage)
{
  case 'lang': $file = 'specials.clangs.inc.php'; break;
  case 'log': $file = 'specials.log.inc.php'; break;
  case 'phpinfo': $file = 'specials.phpinfo.inc.php'; break;
  default : $file = 'specials.settings.inc.php'; break;
}

require rex_path::core('pages/'.$file);