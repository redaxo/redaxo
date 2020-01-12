<?php

$plugin = rex_plugin::get('be_style', 'customizer');

/* Default-Einstellungen */
if (!$plugin->hasConfig()) {
    $plugin->setConfig('labelcolor', '#3bb594');
    $plugin->setConfig('codemirror_theme', 'eclipse');
    $plugin->setConfig('codemirror-selectors', '');
    $plugin->setConfig('codemirror', 1);
    $plugin->setConfig('codemirror-langs', 0);
    $plugin->setConfig('codemirror-tools', 0);
    $plugin->setConfig('showlink', 1);
}

/* Codemirror-Assets entpacken */
$message = '';
$zipArchive = new ZipArchive();

try {
    if (true === $zipArchive->open($plugin->getPath('assets/vendor/codemirror.zip')) &&
        true === $zipArchive->extractTo($plugin->getAssetsUrl('vendor/'))) {
        $zipArchive->close();
    } else {
        $message = rex_i18n::msg('customizer_error_unzip') . '<br>' . $plugin->getPath('assets/vendor/codemirror.zip');
    }
} catch (Exception $e) {
    $message = rex_i18n::msg('customizer_error_unzip') . '<br>' . $plugin->getPath('assets/vendor/codemirror.zip');
    $message .= '<br>' . $e->getMessage();
}
 if ('' != $message) {
     $plugin->setProperty('installmsg', $message);
 }
