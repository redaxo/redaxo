<?php


/**
 * @package redaxo5
 */

// Für größere Exports den Speicher für PHP erhöhen.
if (rex_ini_get('memory_limit') < 67108864) {
    @ini_set('memory_limit', '64M');
}

$subpage = rex_be_controller::getCurrentPagePart(2);

echo rex_view::title(rex_i18n::msg('backup_title'));

include rex_be_controller::getCurrentPageObject()->getSubPath();
