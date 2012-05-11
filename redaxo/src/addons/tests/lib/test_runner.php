<?php

class rex_test_runner
{
  public function setUp()
  {
    // load all required PEAR libs from vendor folder
    $path = __DIR__. '/../../vendor/';
    set_include_path($path . PATH_SEPARATOR . get_include_path());
    
    require_once('PHPUnit/Autoload.php');
  }
  
  public function run($tests)
  {
    $suite  = new PHPUnit_Framework_TestSuite();
    // disable backup of globals, since we have some rex_sql objectes referenced from variables in global space.
    // PDOStatements are not allowed to be serialized
    $suite->setBackupGlobals(false);
    $suite->addTestFiles($tests);
    
    rex_logger::unregister();
    
    ob_start();
    $runner = new PHPUnit_TextUI_TestRunner;
    $runner->doRun($suite); $line = __LINE__;
    $result = ob_get_clean();
    
    $search = __FILE__ .':'. $line . "\n";
    foreach(debug_backtrace(false) as $t)
    {
      $search .= $t['file'] .':'. $t['line'] . "\n";
    }
    $result = str_replace(array($search, rex_path::base()), '', $result);
    
    rex_logger::register();
    
    return $result;
  }
}