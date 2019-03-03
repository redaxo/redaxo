<?php

$addon = rex_addon::get('media_manager');

if (rex_string::versionCompare($addon->getVersion(), '2.4.1-dev', '<')) {
    rex_media_manager::deleteCache();
}

include $addon->getPath('install.php');
