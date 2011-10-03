<?php 

class rex_func_other_test extends PHPUnit_Framework_TestCase
{
  public function setUp()
  {
    parent::setUp();
  }
  
  public function tearDown()
  {
    parent::tearDown();
  }

  public function testSplitString()
  {
    $s1 = 'a b c';
    $this->assertEquals(array('a', 'b', 'c'), rex_split_string($s1), 'splits string by space');
    
    $s1 = '"a b" cdef \'ghi kl\'';
    $this->assertEquals(array('a b', 'cdef', 'ghi kl'), rex_split_string($s1), 'supports quoted strings');
    
    $s1 = 'a=1 b=xyz c="hu hu" 123=\'he he\'';
    $this->assertEquals(array('a' => 1, 'b' => 'xyz', 'c' => 'hu hu', '123' => 'he he'), rex_split_string($s1), 'supports key value pairs');
  }
}