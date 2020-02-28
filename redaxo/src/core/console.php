<?php

set_time_limit(0);

require __DIR__.'/boot.php';

// force debug mode to enable output of notices/warnings and dump() function
rex::setProperty('debug', true);

rex_addon::initialize(!rex::isSetup());

if (!rex::isSetup()) {
    foreach (rex::getConfig('package-order') as $packageId) {
        rex_package::require($packageId)->enlist();
    }
}

$application = new rex_console_application();

rex::setProperty('console', $application);

rex::setProperty('lang', 'en_gb');
rex_i18n::setLocale('en_gb');

$application->setCommandLoader(new rex_console_command_loader());

// Override default list command to display information, that more commands are available after setup.
$command = new rex_command_list();
$application->add($command);
$application->setDefaultCommand($command->getName());

$application->run();
