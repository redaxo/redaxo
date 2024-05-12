<?php

use Redaxo\Core\Backend\Controller;
use Redaxo\Core\Translation\I18n;
use Redaxo\Core\View\View;

// Für größere Exports den Speicher für PHP erhöhen.
if (rex_ini_get('memory_limit') < 67_108_864) {
    @ini_set('memory_limit', '64M');
}

echo View::title(I18n::msg('backup_title'));

Controller::includeCurrentPageSubPath();
