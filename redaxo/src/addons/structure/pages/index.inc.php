<?php
/**
 *
 * @package redaxo5
 */

// -------------- Defaults

$subpage = rex_request('subpage', 'string');
$func = rex_request('func', 'string');

//echo rex_view::title(rex_i18n::msg('specials'),$subline);

switch ($subpage) {
  default : $file = 'structure.inc.php'; break;
}

require dirname(__FILE__) . '/' . $file;
