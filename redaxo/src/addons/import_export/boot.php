<?php

$mypage = 'import_export';

if (rex::isBackend() && is_object(rex::getUser())) {
  rex_perm::register('import_export[export]');
  rex_perm::register('import_export[import]');
}

if (rex_addon::get('cronjob')->isAvailable()) {
  rex_cronjob_manager::registerType('rex_cronjob_export');
}
