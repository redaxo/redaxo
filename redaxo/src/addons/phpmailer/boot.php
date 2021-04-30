<?php
/**
 * PHPMailer Addon.
 *
 * @author markus[dot]staab[at]redaxo[dot]de Markus Staab
 *
 * @package redaxo5
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

// set properties
$aProperties = array("from","fromname","confirmto","mailer","host","port","charset","wordwrap","encoding","priority","smtp_debug","smtpsecure","smtpauth","security_mode","username","password","bcc","archive","test_address","detour_mode","logging","errormail","last_log_file_send_time");
foreach($aProperties AS $sProperty) {
    $addon->setProperty($sProperty,$addon->getConfig($sProperty));
}