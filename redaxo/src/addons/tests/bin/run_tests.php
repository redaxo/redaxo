#!/usr/bin/php
<?php

if (PHP_SAPI !== 'cli') {
    echo 'error: this script may only be run from CLI';
    return 1;
}

// bring the file into context, no matter from which dir it was executed
$path = explode(DIRECTORY_SEPARATOR, __DIR__);
do {
    $part = array_pop($path);
} while (null !== $part && 'redaxo' != $part);

if (!chdir(implode(DIRECTORY_SEPARATOR, $path) . '/redaxo')) {
    echo 'error: start this script within a redaxo projects folder';
    return 2;
}

// ---- bootstrap REX

$REX = [];
$REX['REDAXO'] = true;
$REX['HTDOCS_PATH'] = '../';
$REX['BACKEND_FOLDER'] = 'redaxo';

// bootstrap core
require 'src/core/boot.php';

// bootstrap addons
include_once rex_path::core('packages.php');

while (ob_get_level()) {
    ob_end_clean();
}

$runner = new rex_test_runner();
$runner->setUp();
$result = $runner->run(rex_test_locator::defaultLocator(), 'auto');

exit($result->wasSuccessful() ? 0 : 99);
