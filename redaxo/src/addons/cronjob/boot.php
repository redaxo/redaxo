<?php

/**
 * Cronjob Addon.
 *
 * @author gharlan[at]web[dot]de Gregor Harlan
 *
 * @package redaxo5
 */

$addon = rex_addon::get('cronjob');

define('REX_CRONJOB_LOG_FOLDER', $addon->getDataPath());
/** @deprecated use rex::getTable('cronjob') instead´*/
define('REX_CRONJOB_TABLE', rex::getTable('cronjob'));

if (rex::getConsole()) {
    // don't run cronjobs while running console commands
    return;
}

rex_extension::register('PACKAGES_INCLUDED', static function () use ($addon) {
    foreach ($addon->getAvailablePlugins() as $plugin) {
        if ('' != ($type = $plugin->getProperty('cronjob_type'))) {
            rex_cronjob_manager::registerType($type);
        }
    }
});

$nexttime = $addon->getConfig('nexttime', 0);

if (0 != $nexttime && time() >= $nexttime) {
    $env = rex_cronjob_manager::getCurrentEnvironment();
    $EP = 'backend' === $env ? 'PAGE_CHECKED' : 'PACKAGES_INCLUDED';
    rex_extension::register($EP, static function () use ($env) {
        if ('backend' !== $env || !in_array(rex_be_controller::getCurrentPagePart(1), ['setup', 'login', 'cronjob'])) {
            rex_cronjob_manager_sql::factory()->check();
        }
    });
}
