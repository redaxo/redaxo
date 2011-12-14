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
  echo rex_view::title(rex_i18n::msg('system'));
}

switch($subpage)
{
  case 'lang': $file = 'system.clangs.inc.php'; break;
  case 'log': $file = 'system.log.inc.php'; break;
  case 'phpinfo': $file = 'system.phpinfo.inc.php'; break;
  default : $file = 'system.settings.inc.php'; break;
}

require rex_path::core('pages/'.$file);