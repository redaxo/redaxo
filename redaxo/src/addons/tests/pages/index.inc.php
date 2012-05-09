<?php

echo rex_view::title('TestResults');

$tests = rex_dir::recursiveIterator(dirname(__FILE__).'/../lib/tests', rex_dir_recursive_iterator::LEAVES_ONLY)->ignoreSystemStuff();

$runner = new rex_test_runner();
$runner->setUp();

echo '<pre>';
echo $runner->run($tests);
echo '</pre>';
