<?php

set_time_limit(0);

require __DIR__.'/boot.php';
// force debug mode to enable output of notices/warnings and dump() function
rex::setProperty('debug', true);

// Check if posix is available
if (!function_exists('posix_getuid')) {
        echo "REDAXO console". PHP_EOL;
        echo "-----------------------------------------------" . PHP_EOL;
		echo "The posix extensions are required - see http://php.net/manual/en/book.posix.php" . PHP_EOL;
		exit(1);
	}
// Check current user
	$currentuser = posix_getpwuid(posix_getuid());
	$webuser = posix_getpwuid(fileowner($REX['HTDOCS_PATH'].$REX['BACKEND_FOLDER']));
	if ($currentuser['name'] !== $webuser['name']) {
        echo "REDAXO console". PHP_EOL;
		echo "Please run console with the user that owns the folder /redaxo" . PHP_EOL;
        echo "-----------------------------------------------" . PHP_EOL;
		echo "Current user: " . $currentuser['name'] . PHP_EOL;
        echo "-----------------------------------------------" . PHP_EOL;
		echo "Owner of redaxo: " . $webuser['name'] . PHP_EOL;
        echo "-----------------------------------------------" . PHP_EOL;
		echo "Try to run console by adding 'sudo -u " . $webuser['name'] . " ' to the beginning of the command" . PHP_EOL;
		echo "If running with 'docker exec' try adding the option '-u " . $webuser['name'] . "' to the docker command" . PHP_EOL;
		exit(1);
	}

rex_addon::initialize(!rex::isSetup());

if (!rex::isSetup()) {
    foreach (rex::getConfig('package-order') as $packageId) {
        rex_package::get($packageId)->enlist();
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

