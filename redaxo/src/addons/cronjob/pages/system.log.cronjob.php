<?php

$addon = rex_addon::get('cronjob');
$logFile = $addon->getDataPath('cronjob.log');

require rex_path::core('pages/system.log.external.php');
