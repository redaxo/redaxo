<?php

/**
 * Cronjob Addon.
 *
 * @author gharlan[at]web[dot]de Gregor Harlan
 *
 * @package redaxo5
 *
 * @var rex_addon $this
 */

define('REX_CRONJOB_LOG_FOLDER', $this->getDataPath());
define('REX_CRONJOB_TABLE', rex::getTable('cronjob'));

rex_extension::register('PACKAGES_INCLUDED', function () {
    foreach ($this->getAvailablePlugins() as $plugin) {
        if (($type = $plugin->getProperty('cronjob_type')) != '') {
            rex_cronjob_manager::registerType($type);
        }
    }
});

$nexttime = $this->getConfig('nexttime', 0);

if ($nexttime != 0 && time() >= $nexttime) {
    $EP = rex::isBackend() ? 'PAGE_CHECKED' : 'PACKAGES_INCLUDED';
    rex_extension::register($EP, function () {
        if (!rex::isBackend() || !in_array(rex_be_controller::getCurrentPagePart(1), ['setup', 'login', 'cronjob'])) {
            rex_cronjob_manager_sql::factory()->check();
        }
    });
}
