<?php

if (!rex_addon::get('cronjob')->isAvailable()) {
    return;
}
if (rex::isSafeMode()) {
    return;
}
rex_cronjob_manager::registerType(rex_cronjob_export::class);