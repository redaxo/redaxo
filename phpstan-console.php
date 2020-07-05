<?php

if (!defined('REX_MIN_PHP_VERSION')) {
    unset($REX);
    $REX['REDAXO'] = true;
    $REX['HTDOCS_PATH'] = './';
    $REX['BACKEND_FOLDER'] = 'redaxo';
    $REX['LOAD_PAGE'] = false;

    require __DIR__.'/redaxo/src/core/boot.php';
}

$application = new rex_console_application();
$application->setCommandLoader(new rex_console_command_loader());

return $application;
