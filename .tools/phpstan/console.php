<?php

use Redaxo\Core\Console\Application;
use Redaxo\Core\Console\CommandLoader;
use Redaxo\Core\Security\BackendLogin;

if (!defined('REX_MIN_PHP_VERSION')) {
    unset($REX);
    $REX['REDAXO'] = true;
    $REX['HTDOCS_PATH'] = './';
    $REX['BACKEND_FOLDER'] = 'redaxo';
    $REX['LOAD_PAGE'] = false;

    require dirname(__DIR__, 2) . '/redaxo/src/core/boot.php';

    // initialize autoloader before phpstan autoload wrapper is active
    // https://github.com/redaxo/redaxo/pull/4369#issuecomment-770195916
    class_exists(BackendLogin::class);
}

$application = new Application();
$application->setCommandLoader(new CommandLoader());

return $application;
