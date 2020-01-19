<?php

/**
 * PHPMailer Addon.
 *
 * @author markus[dot]staab[at]redaxo[dot]de Markus Staab
 *
 * @package redaxo5
 */

$addon = rex_addon::get('phpmailer');

if (!$addon->hasConfig()) {
    $addon->setConfig('from', '');
    $addon->setConfig('test_address', '');
    $addon->setConfig('fromname', 'Mailer');
    $addon->setConfig('confirmto', '');
    $addon->setConfig('bcc', '');
    $addon->setConfig('mailer', 'mail');
    $addon->setConfig('host', 'localhost');
    $addon->setConfig('port', 25);
    $addon->setConfig('charset', 'utf-8');
    $addon->setConfig('wordwrap', 120);
    $addon->setConfig('encoding', '8bit');
    $addon->setConfig('priority', 0);
    $addon->setConfig('smtpsecure', '');
    $addon->setConfig('smtpauth', false);
    $addon->setConfig('username', '');
    $addon->setConfig('password', '');
    $addon->setConfig('smtp_debug', '0');
    $addon->setConfig('logging', 0);
    $addon->setConfig('archive', false);
} else {
    if (!$addon->hasConfig('logging')) {
        $addon->setConfig('logging', 0);
    }
    if (!$addon->hasConfig('archive')) {
        $addon->setConfig('archive', false);
    }
}

if ($addon->hasConfig('log')) {
    if ($addon->getConfig('log')) {
        $addon->setConfig('archive', true);
    }
    $addon->removeConfig('log');
}

$oldBackUpFolder = rex_path::addonData('phpmailer', 'mail_backup');
$logFolder = rex_path::addonData('phpmailer', 'mail_log');
if (file_exists($oldBackUpFolder) && !file_exists($logFolder)) {
    rename($oldBackUpFolder, $logFolder);
}
