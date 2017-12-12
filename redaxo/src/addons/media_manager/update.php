<?php

/** @var rex_addon $this */

if (rex_string::versionCompare($this->getVersion(), '2.4.1-dev', '<')) {
    rex_media_manager::deleteCache();
}

include $this->getPath('install.php');
