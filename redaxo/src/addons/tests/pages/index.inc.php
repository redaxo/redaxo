<?php

echo rex_view::title('TestResults');

$runner = new rex_test_runner();
$runner->setUp();

echo '<pre>';
echo $runner->run(new rex_test_locator());
echo '</pre>';
