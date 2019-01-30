<?php

$myaddon = rex_addon::get('media_manager');

if (rex_string::versionCompare($myaddon->getVersion(), '2.4.1-dev', '<')) {
    rex_media_manager::deleteCache();
}

include $myaddon->getPath('install.php');
