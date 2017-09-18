<?php

if (!$this->hasConfig('log')) {
    $this->setConfig('log', 1);
}

if (rex_string::versionCompare($this->getVersion(), '2.2', '<')) {
    $oldBackUpFolder = rex_path::addonData('phpmailer', 'mail_backup');
    $LogFolder = rex_path::addonData('phpmailer', 'mail_log');
    if (file_exists($oldBackUpFolder)) {
        rename($oldBackUpFolder, $LogFolder);
    }
}
