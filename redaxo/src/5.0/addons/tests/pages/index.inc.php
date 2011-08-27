<?php

$dir = getcwd();
chdir(dirname(__FILE__). '/../vendor');
require_once('PHPUnit.php');

chdir($dir);

$suite  = new PHPUnit_TestSuite();
$suite->addTestSuite('rex_sql_test');
$result = PHPUnit::run($suite);
print $result->toHTML();