<?php

$addon = rex_addon::get('media_manager');

if (rex_string::versionCompare($addon->getVersion(), '2.4.1-dev', '<')) {
    rex_media_manager::deleteCache();
}

// use path relative to __DIR__ to get correct path in update temp dir
$addon->includeFile(__DIR__.'/install.php');
