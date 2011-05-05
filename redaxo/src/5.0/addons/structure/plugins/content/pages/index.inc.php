<?php
/**
 *
 * @package redaxo5
 * @version svn:$Id$
 */

// -------------- Defaults

$subpage = rex_request('subpage', 'string');
$func = rex_request('func', 'string');


switch($subpage)
{
  default : $file = 'content.inc.php'; break;
}

//rex_title(rex_i18n::msg('specials'),$subline);
require dirname(__FILE__).'/'. $file;