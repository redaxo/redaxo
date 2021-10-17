<?php

$addon = rex_addon::get('backup');

if (rex_addon::get('cronjob')->isInstalled() && rex_string::versionCompare($addon->getVersion(), '2.8.0-dev', '<')) {
    rex_sql::factory()
        ->setTable(rex::getTable('cronjob'))
        ->setWhere(['type' => rex_cronjob_export::class])
        ->setRawValue('parameters', 'REPLACE(parameters, \'"rex_cronjob_export_blacklist_tables":\', \'"rex_cronjob_export_exclude_tables":\')')
        ->update();
}
