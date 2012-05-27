<?php
/**
 *
 * @package redaxo5
 */

// -------------- Defaults

$subpage = rex_request('subpage', 'string');
$func = rex_request('func', 'string');


switch ($subpage) {
  default : $file = 'content.inc.php'; break;
}

//echo rex_view::title(rex_i18n::msg('specials'),$subline);
require dirname(__FILE__) . '/' . $file;
