<?php

/**
 * PHPMailer Addon.
 *
 * @author markus[dot]staab[at]redaxo[dot]de Markus Staab
 *
 * @package redaxo5
 */

$addon = rex_addon::get('phpmailer');

if ($addon->hasConfig('log')) {
    if ($addon->getConfig('log')) {
        $addon->setConfig('archive', true);
    }
    $addon->removeConfig('log');
}
if (!$addon->hasConfig('errormail')) {
    $addon->setConfig('errormail', 0);
}
if (!$addon->hasConfig('security_mode')) {
    $addon->setConfig('security_mode', false); // AutoTLS per default deaktiviert
}

$oldBackUpFolder = rex_path::addonData('phpmailer', 'mail_backup');
$logFolder = rex_path::addonData('phpmailer', 'mail_log');
if (is_dir($oldBackUpFolder) && !is_dir($logFolder)) {
    rename($oldBackUpFolder, $logFolder);
}
