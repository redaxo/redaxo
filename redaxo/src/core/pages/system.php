<?php

/**
 * @package redaxo5
 */

// -------------- Defaults

$subpage = rex_be_controller::getCurrentPagePart(2);
$func = rex_request('func', 'string');

// -------------- Header
if ($subpage != 'phpinfo') {
    echo rex_view::title(rex_i18n::msg('system'));
}

$file = null;

switch ($subpage) {
    case 'lang': $file = 'system.clangs.php'; break;
    case 'log': $file = 'system.log.php'; break;
    case 'phpinfo': $file = 'system.phpinfo.php'; break;
    case 'settings': $file = 'system.settings.php'; break;
}

if ($file) {
    $file = rex_path::core('pages/'.$file);
} else {
    $file = rex_be_controller::getCurrentPageObject()->getSubPath();
}
require $file;
