<?php
/**
 *
 * @package redaxo5
 * @version svn:$Id$
 */

// -------------- Defaults

$subpage = rex_request('subpage', 'string');
$func = rex_request('func', 'string');

//rex_title(rex_i18n::msg('specials'),$subline);

switch($subpage)
{
  default : $file = 'templates.inc.php'; break;
}

require dirname(__FILE__).'/'. $file;