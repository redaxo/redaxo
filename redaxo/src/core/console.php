<?php

set_time_limit(0);

require __DIR__.'/boot.php';

// force debug mode to enable output of notices/warnings and dump() function
rex::setProperty('debug', true);

$application = new rex_console_application();

rex::setProperty('console', $application);

require rex_path::core('packages.php');

$input = new Symfony\Component\Console\Input\ArgvInput();

$application->run($input);
