<?php

/* Default-Einstellungen */
if (!$this->hasConfig()) {
    $this->setConfig('labelcolor', '#3bb594');
    $this->setConfig('codemirror_theme', 'eclipse');
    $this->setConfig('codemirror-selectors', '');
    $this->setConfig('codemirror', 1);
    $this->setConfig('codemirror-langs', 0);
    $this->setConfig('codemirror-tools', 0);
    $this->setConfig('showlink', 1);
}

/* Codemirror-Assets entpacken */
$message = '';
$zipArchive = new ZipArchive();

try {
    if ($zipArchive->open($this->getPath('assets/vendor/codemirror.zip')) === true &&
        $zipArchive->extractTo($this->getAssetsUrl('vendor/')) === true) {
        $zipArchive->close();
    } else {
        $message = rex_i18n::msg('customizer_error_unzip') . '<br>' . $this->getPath('assets/vendor/codemirror.zip');
    }
} catch (Exception $e) {
    $message = rex_i18n::msg('customizer_error_unzip') . '<br>' . $this->getPath('assets/vendor/codemirror.zip');
    $message .= '<br>' . $e->getMessage();
}
 if ($message != '') {
     $this->setProperty('installmsg', $message);
 }
