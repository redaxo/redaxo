<?php

/**
 * PHPMailer Addon.
 *
 * @author markus[dot]staab[at]redaxo[dot]de Markus Staab
 *
 * @package redaxo5
 *
 * @var rex_addon $this
 */

if (!$this->hasConfig()) {
    $this->setConfig('from', '');
    $this->setConfig('test_address', '');
    $this->setConfig('fromname', 'Mailer');
    $this->setConfig('confirmto', '');
    $this->setConfig('bcc', '');
    $this->setConfig('mailer', 'mail');
    $this->setConfig('host', 'localhost');
    $this->setConfig('port', 25);
    $this->setConfig('charset', 'utf-8');
    $this->setConfig('wordwrap', 120);
    $this->setConfig('encoding', '8bit');
    $this->setConfig('priority', 0);
    $this->setConfig('smtpsecure', '');
    $this->setConfig('smtpauth', false);
    $this->setConfig('username', '');
    $this->setConfig('password', '');
    $this->setConfig('smtp_debug', '0');
    $this->setConfig('log', 0);
} else {
    if (!$this->hasConfig('log')) {
        $this->setConfig('log', 0);
    }
}

$oldBackUpFolder = rex_path::addonData('phpmailer', 'mail_backup');
$logFolder = rex_path::addonData('phpmailer', 'mail_log');
if (file_exists($oldBackUpFolder) && !file_exists($logFolder)) {
    rename($oldBackUpFolder, $logFolder);
}
