<?php

class rex_var_test extends PHPUnit_Framework_TestCase
{
  public function setUp()
  {
    parent::setUp();
  }

  public function tearDown()
  {
    parent::tearDown();
  }

  public function testGlobalVarParamsInstead()
  {
    $this->assertEquals('abc', rex_var::handleGlobalVarParams('myVar', array('instead' => 'abc'), 'myVal'), 'instead value used when a value present');
    $this->assertEquals('', rex_var::handleGlobalVarParams('myVar', array('instead' => 'abc'), null), 'instead value not used when no value present');
  }

  public function testGlobalVarParamsIfempty()
  {
    $this->assertEquals('myVal', rex_var::handleGlobalVarParams('myVar', array('ifempty' => 'nothing'), 'myVal'), 'ifempty not used when a value present');
    $this->assertEquals('nothing', rex_var::handleGlobalVarParams('myVar', array('ifempty' => 'nothing'), null), 'ifempty used when no value present');
    $this->assertEquals('nothing', rex_var::handleGlobalVarParams('myVar', array('ifempty' => 'nothing'), ''), 'ifempty used on empty string');
  }

  public function testGlobalVarParamsPrefix()
  {
    $this->assertEquals('AAmyVal', rex_var::handleGlobalVarParams('myVar', array('prefix' => 'AA'), 'myVal'), 'prefix will be appended in front');
    $this->assertEquals('', rex_var::handleGlobalVarParams('myVar', array('prefix' => 'AA'), ''), 'empty string remain empty');
    $this->assertEquals('', rex_var::handleGlobalVarParams('myVar', array('prefix' => 'AA'), null), 'null value remain empty');
  }

  public function testGlobalVarParamsSuffix()
  {
    $this->assertEquals('myValBB', rex_var::handleGlobalVarParams('myVar', array('suffix' => 'BB'), 'myVal'), 'suffix will be appended at the end');
    $this->assertEquals('', rex_var::handleGlobalVarParams('myVar', array('suffix' => 'BB'), ''), 'empty string remain empty');
    $this->assertEquals('', rex_var::handleGlobalVarParams('myVar', array('suffix' => 'BB'), null), 'null value remain empty');
  }

  public function testGlobalVarParamsPreSuffix()
  {
    $this->assertEquals('AAmyValBB', rex_var::handleGlobalVarParams('myVar', array('prefix' => 'AA', 'suffix' => 'BB'), 'myVal'), 'pre- and suffix will be appended');
    $this->assertEquals('', rex_var::handleGlobalVarParams('myVar', array('prefix' => 'AA', 'suffix' => 'BB'), ''), 'empty string remain empty');
    $this->assertEquals('', rex_var::handleGlobalVarParams('myVar', array('prefix' => 'AA', 'suffix' => 'BB'), null), 'null value remain empty');
  }

  public function testGlobalVarParamsCallback()
  {
    $triggered = false;

    $suite = $this;
    rex_var::handleGlobalVarParams('myVar',
      array(
        'callback' => function ($params) use ($suite, &$triggered) {
          $triggered = true;
          $suite->assertEquals($params['subject'], 'myVal', 'var value will be given as subject');
          $suite->assertTrue(isset($params['param1']), 'parameters will be passed');
          $suite->assertEquals($params['param1'], 'BB', 'parameters has the correct value');
        },
        'param1' => 'BB'
      ),
      'myVal');

    $this->assertTrue($triggered, 'callbacks will be triggered');
  }
}
