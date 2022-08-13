<?php

/**
 * PHPMailer Addon.
 *
 * @author markus[dot]staab[at]redaxo[dot]de Markus Staab
 */

$addon = rex_addon::get('phpmailer');

if (!rex::isBackend() && 0 != $addon->getConfig('errormail')) {
    rex_extension::register('RESPONSE_SHUTDOWN', static function () {
        rex_mailer::errorMail();
    });
}

if ('system' == rex_be_controller::getCurrentPagePart(1)) {
    rex_system_setting::register(new rex_system_setting_phpmailer_errormail());
}

// make the phpmailer addon icon orange if detour_mode is active
if (true == $addon->getConfig('detour_mode')) {
    $page = $addon->getProperty('page');
    $page['icon'] .= ' text-danger';
    $addon->setProperty('page', $page);
}

if (rex_addon::get('cronjob')->isAvailable()) {
    rex_cronjob_manager::registerType(rex_cronjob_mailer_purge::class);
}
