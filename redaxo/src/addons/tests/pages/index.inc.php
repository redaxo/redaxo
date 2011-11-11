<?php

rex_title('TestResults');

// load all required PEAR libs from vendor folder
$path = dirname(__FILE__). '/../vendor/';
set_include_path($path . PATH_SEPARATOR . get_include_path());

require_once('PHPUnit/Autoload.php');

$testCollector = new PHPUnit_Runner_IncludePathTestCollector(
  array(dirname(__FILE__). '/../lib/tests/*'), array('_test.php', '.phpt')
);

/*
foreach($testCollector->collectTests() as $test){
  var_dump($test->__toString());
  echo '<br>';
}
*/

$suite  = new PHPUnit_Framework_TestSuite();
// disable backup of globals, since we have some rex_sql objectes referenced from variables in global space.
// PDOStatements are not allowed to be serialized
$suite->setBackupGlobals(false);
$suite->addTestFiles($testCollector->collectTests());
$result = $suite->run();

$resultPrinter = new PHPUnit_TextUI_ResultPrinter(null, true  );
echo '<pre>';
print $resultPrinter->printResult($result);
echo '</pre>';