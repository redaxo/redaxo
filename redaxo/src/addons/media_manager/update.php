<?php

/** @var rex_addon $this */

if (rex_string::versionCompare($this->getVersion(), '2.3.0-dev', '<')) {
    rex_media_manager::deleteCache();
}
