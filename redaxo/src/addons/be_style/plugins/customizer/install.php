<?php

$myplugin = rex_plugin::get('be_style', 'customizer');

/* Default-Einstellungen */
if (!$myplugin->hasConfig()) {
    $myplugin->setConfig('labelcolor', '#3bb594');
    $myplugin->setConfig('codemirror_theme', 'eclipse');
    $myplugin->setConfig('codemirror-selectors', '');
    $myplugin->setConfig('codemirror', 1);
    $myplugin->setConfig('codemirror-langs', 0);
    $myplugin->setConfig('codemirror-tools', 0);
    $myplugin->setConfig('showlink', 1);
}

/* Codemirror-Assets entpacken */
$message = '';
$zipArchive = new ZipArchive();

try {
    if ($zipArchive->open($myplugin->getPath('assets/vendor/codemirror.zip')) === true &&
        $zipArchive->extractTo($myplugin->getAssetsUrl('vendor/')) === true) {
        $zipArchive->close();
    } else {
        $message = rex_i18n::msg('customizer_error_unzip') . '<br>' . $myplugin->getPath('assets/vendor/codemirror.zip');
    }
} catch (Exception $e) {
    $message = rex_i18n::msg('customizer_error_unzip') . '<br>' . $myplugin->getPath('assets/vendor/codemirror.zip');
    $message .= '<br>' . $e->getMessage();
}
 if ($message != '') {
     $myplugin->setProperty('installmsg', $message);
 }
