<?php

// work around phpunit issue https://github.com/sebastianbergmann/phpunit/issues/2101
// which is fixed in newer php versions. we cannot use a more recent version atm because php 5.5 support is required.
define('PHPUNIT_TESTSUITE', 1);

echo rex_view::title('TestResults');

$runner = new rex_test_runner();
$runner->setUp();

echo '<pre>';
$runner->run(rex_test_locator::defaultLocator());
echo '</pre>';
