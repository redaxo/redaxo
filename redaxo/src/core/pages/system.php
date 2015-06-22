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

switch ($subpage) {
    case 'lang': $file = 'system.clangs.php'; break;
    case 'log': $file = 'system.log.php'; break;
    case 'phpinfo': $file = 'system.phpinfo.php'; break;
    default : $file = 'system.settings.php'; break;
}

require rex_path::core('pages/' . $file);
