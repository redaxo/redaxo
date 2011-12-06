<?php

class testFactory extends rex_factory{
  static public function factory()
  {
    // just return the class which was determined using rex_factory.
    // this doesn't make sense in real use-cases but eases testing
    return self::getFactoryClass();
  }
  public function doSomething()
  {
    return 'base';
  }

  static public function staticCall()
  {
    if(static::hasFactoryClass())
    {
      return static::callFactoryClass(__FUNCTION__, func_get_args());
    }
    return 'static-base';
  }
}
class testAlternativeFactory extends testFactory{
  public function doSomething()
  {
    return 'overridden';
  }
  
  static public function staticCall()
  {
    return 'static-overridden';
  }
}

class rex_factory_test extends PHPUnit_Framework_TestCase
{
  public function testFactoryCreation()
  {
    $this->assertFalse(testFactory::hasFactoryClass(), 'initially no factory class is set');
    $this->assertEquals('testFactory', testFactory::getFactoryClass(), 'original factory class will be returned');
    $clazz = testFactory::factory();
    $this->assertEquals('testFactory', $clazz, 'factory class defaults to the original impl');
    $obj = new $clazz();
    $this->assertEquals('base', $obj->doSomething(), 'call method of the original impl');
    $this->assertEquals('static-base', testFactory::staticCall(), 'static method of original impl');
    
    testFactory::setFactoryClass('testAlternativeFactory');
    $this->assertTrue(testFactory::hasFactoryClass(), 'factory class was set');
    $this->assertEquals('testAlternativeFactory', testFactory::getFactoryClass(), 'factory class will be returned');
    $clazz = testFactory::factory();
    $this->assertEquals('testAlternativeFactory', $clazz, 'alternative factory class will be used');
    $obj = new $clazz();
    $this->assertEquals('overridden', $obj->doSomething(), 'call method of the alternative impl');
    $this->assertEquals('static-overridden', testFactory::staticCall(), 'static method of alternative impl');
  }
}