<?php

set_time_limit(0);

require __DIR__.'/boot.php';

// force debug mode to enable output of notices/warnings and dump() function
rex::setProperty('debug', true);

rex_addon::initialize(!rex::isSetup());

if (rex::isSetup()) {
    $packageOrder = rex_package_manager::generatePackageOrder(!rex::isSetup());
} else {
    $packageOrder = rex::getConfig('package-order');
}

foreach ($packageOrder as $packageId) {
    rex_package::get($packageId)->enlist();
}

$application = new rex_console_application();

rex::setProperty('console', $application);

rex::setProperty('lang', 'en_gb');
rex_i18n::setLocale('en_gb');

$application->setCommandLoader(new rex_console_command_loader());

$application->run();
