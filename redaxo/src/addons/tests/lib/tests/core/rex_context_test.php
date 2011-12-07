<?php

class rex_context_test extends PHPUnit_Framework_TestCase
{
  private $context;
  
  public function setUp()
  {
    $globalParams = array('int' => '25', 'str' => '<a b$c&?>');
    $this->context = new rex_context($globalParams);
    
    parent::setUp();
  }

  public function tearDown()
  {
    $this->context = null;
    
    parent::tearDown();
  }
  
  public function testGetUrl()
  {
    $this->assertEquals('index.php?int=25&amp;str=%3Ca+b%24c%26%3F%3E', $this->context->getUrl(), 'parameters get properly encoded');
    $this->assertEquals('index.php?int=25&amp;str=xyz', $this->context->getUrl(array('str' => 'xyz')), 'local params override global params');
    $this->assertEquals('index.php?int=25&amp;str=%3Ca+b%24c%26%3F%3E&amp;str2=xyz', $this->context->getUrl(array('str2' => 'xyz')), 'new params are appended');
  }
  
  public function testGetHiddenInputFields()
  {
    $this->assertEquals(
    	'<input type="hidden" name="int" value="25" /><input type="hidden" name="str" value="&lt;a b$c&amp;?&gt;" />',
      $this->context->getHiddenInputFields(),
      'parameters get properly encoded'
    );
    
    $this->assertEquals(
    	'<input type="hidden" name="int" value="25" /><input type="hidden" name="str" value="xyz" />',
      $this->context->getHiddenInputFields(array('str' => 'xyz')),
      'local params override global params'
    );
    
    $this->assertEquals(
    	'<input type="hidden" name="int" value="25" /><input type="hidden" name="str" value="&lt;a b$c&amp;?&gt;" /><input type="hidden" name="str2" value="xyz" />',
      $this->context->getHiddenInputFields(array('str2' => 'xyz')),
      'new params are appended'
    );
  }
}