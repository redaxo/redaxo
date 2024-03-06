<?php

use Redaxo\Core\Translation\I18n;

// Für größere Exports den Speicher für PHP erhöhen.
if (rex_ini_get('memory_limit') < 67_108_864) {
    @ini_set('memory_limit', '64M');
}

echo rex_view::title(I18n::msg('backup_title'));

rex_be_controller::includeCurrentPageSubPath();
