<?php

use Redaxo\Core\Database\DatabaseUtil;
use Redaxo\Core\Http\Exception\NotFoundHttpException;

$logFile = DatabaseUtil::slowQueryLogPath();

if (null === $logFile) {
    throw new NotFoundHttpException('Slow query log file not found.');
}

require_once __DIR__ . '/log.external.php';
