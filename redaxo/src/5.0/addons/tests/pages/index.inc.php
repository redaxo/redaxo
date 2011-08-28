<?php

// load all required PEAR libs from vendor folder
$path = dirname(__FILE__). '/../vendor/';
set_include_path($path . PATH_SEPARATOR . get_include_path());

require_once('PHPUnit/Autoload.php');

$suite  = new PHPUnit_Framework_TestSuite();
// disable backup of globals, since we have some rex_sql objectes referenced from variables in global space.
// PDOStatements are not allowed to be serialized
$suite->setBackupGlobals(false);

$suite->addTestSuite('rex_sql_test');
$suite->addTestSuite('rex_sql_select_test');
$result = $suite->run();
$resultPrinter = new PHPUnit_TextUI_ResultPrinter(null, true  );

echo '<pre>';
print $resultPrinter->printResult($result);
echo '</pre>';