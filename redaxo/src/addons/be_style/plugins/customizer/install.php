<?php

$plugin = rex_plugin::get('be_style', 'customizer');

/* Codemirror-Assets entpacken */
$message = '';
$zipArchive = new ZipArchive();

// use path relative to __DIR__ to get correct path in update temp dir
$path = __DIR__.'/assets/vendor/codemirror.zip';

try {
    if (true === $zipArchive->open($path) &&
        true === $zipArchive->extractTo($plugin->getAssetsPath('vendor/'))
    ) {
        $zipArchive->close();
    } else {
        $message = rex_i18n::msg('customizer_error_unzip') . '<br>' . $path;
    }
} catch (Exception $e) {
    $message = rex_i18n::msg('customizer_error_unzip') . '<br>' . $path;
    $message .= '<br>' . $e->getMessage();
}

 if ('' != $message) {
     throw new rex_functional_exception($message);
 }
