<?php

/**
 * PHPMailer Addon.
 *
 * @author markus[dot]staab[at]redaxo[dot]de Markus Staab
 *
 * @package redaxo5
 */

$myaddon = rex_addon::get('phpmailer');

if (!$myaddon->hasConfig()) {
    $myaddon->setConfig('from', '');
    $myaddon->setConfig('test_address', '');
    $myaddon->setConfig('fromname', 'Mailer');
    $myaddon->setConfig('confirmto', '');
    $myaddon->setConfig('bcc', '');
    $myaddon->setConfig('mailer', 'mail');
    $myaddon->setConfig('host', 'localhost');
    $myaddon->setConfig('port', 25);
    $myaddon->setConfig('charset', 'utf-8');
    $myaddon->setConfig('wordwrap', 120);
    $myaddon->setConfig('encoding', '8bit');
    $myaddon->setConfig('priority', 0);
    $myaddon->setConfig('smtpsecure', '');
    $myaddon->setConfig('smtpauth', false);
    $myaddon->setConfig('username', '');
    $myaddon->setConfig('password', '');
    $myaddon->setConfig('smtp_debug', '0');
    $myaddon->setConfig('log', 0);
} else {
    if (!$myaddon->hasConfig('log')) {
        $myaddon->setConfig('log', 0);
    }
}

$oldBackUpFolder = rex_path::addonData('phpmailer', 'mail_backup');
$logFolder = rex_path::addonData('phpmailer', 'mail_log');
if (file_exists($oldBackUpFolder) && !file_exists($logFolder)) {
    rename($oldBackUpFolder, $logFolder);
}
