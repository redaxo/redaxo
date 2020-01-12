<?php

$addon = rex_addon::get('install');

if (rex_string::versionCompare($addon->getVersion(), '2.0.1', '<') && rex_config::has('install')) {
    rex_file::putCache($addon->getDataPath('config.json'), rex_config::get('install'));
    rex_config::removeNamespace('install');
}
