<?php 

class rex_var_config_test extends rex_var_base_test
{
  public function setUp()
  {
    rex::setConfig('myConfig', 'myConfValue');
    parent::setUp();
  }
  
  public function tearDown()
  {
    rex::removeConfig('myConfig');
    parent::tearDown();
  } 
  
  public function testConfigReplace()
  {
    $varContent = '?>a.REX_CONFIG[field=myConfig].b';
    $configVar = new rex_var_config();
    
    $result = $this->evalCode($configVar->getTemplate($varContent));
  
    $this->assertEquals($result, 'a.myConfValue.b');
  }
}