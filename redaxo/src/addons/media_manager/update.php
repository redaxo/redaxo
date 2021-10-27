<?php

$addon = rex_addon::get('media_manager');

if (rex_string::versionCompare($addon->getVersion(), '2.4.1-dev', '<')) {
    rex_media_manager::deleteCache();
}

if (rex_string::versionCompare($addon->getVersion(), '2.12.0-dev', '<')) {
    rex_sql::factory()->setQuery('
        DELETE t, te
        FROM '.rex::getTable('media_manager_type').' t
        LEFT JOIN '.rex::getTable('media_manager_type_effect').' te ON te.type_id = t.id
        WHERE t.id < 6
    ');

    rex_media_manager::deleteCache();
}

// use path relative to __DIR__ to get correct path in update temp dir
$addon->includeFile(__DIR__.'/install.php');
