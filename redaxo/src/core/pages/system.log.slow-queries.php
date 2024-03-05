<?php

use Redaxo\Core\Database\Util;
$logFile = Util::slowQueryLogPath();

if (null === $logFile) {
    throw new rex_exception('slow query log file not found');
}

require_once __DIR__ . '/system.log.external.php';
