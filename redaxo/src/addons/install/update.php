<?php

/** @var rex_addon $this */

if (rex_string::versionCompare($this->getVersion(), '2.0.1', '<') && rex_config::has('install')) {
    rex_file::putCache($this->getDataPath('config.json'), rex_config::get('install'));
    rex_config::removeNamespace('install');
}
