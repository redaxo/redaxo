<?php

// This file is included several times while import takes place.
// Use the following codesnippet to distinguish between the different events.

if ($importType == rex_backup::IMPORT_ARCHIVE) {
    if ($eventType == rex_backup::IMPORT_EVENT_PRE) {
        // do something before file-archive import
    } elseif ($eventType == rex_backup::IMPORT_EVENT_POST) {
        // do something after file-archive import
    }
} elseif ($importType == rex_backup::IMPORT_DB) {
    if ($eventType == rex_backup::IMPORT_EVENT_PRE) {
        // do something before database import
    } elseif ($eventType == rex_backup::IMPORT_EVENT_POST) {
        // do something after database import
    }
}
