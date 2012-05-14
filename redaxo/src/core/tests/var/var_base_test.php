<?php 

abstract class rex_var_base_test extends PHPUnit_Framework_TestCase
{
  public function setUp()
  {
    parent::setUp();
  }
  
  public function tearDown()
  {
    parent::tearDown();
  }

  protected function evalCode($code)
  {
    ob_start();
    eval($code);
    return ob_get_clean();
  }
}
