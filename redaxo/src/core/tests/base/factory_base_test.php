<?php

class rex_test_factory extends rex_factory_base
{
  static public function factory()
  {
    // just return the class which was determined using rex_factory_base.
    // this doesn't make sense in real use-cases but eases testing
    return self::getFactoryClass();
  }
  public function doSomething()
  {
    return 'base';
  }

  static public function staticCall()
  {
    if (static::hasFactoryClass())
    {
      return static::callFactoryClass(__FUNCTION__, func_get_args());
    }
    return 'static-base';
  }
}
class rex_alternative_test_factory extends rex_test_factory
{
  public function doSomething()
  {
    return 'overridden';
  }

  static public function staticCall()
  {
    return 'static-overridden';
  }
}

class rex_factory_base_test extends PHPUnit_Framework_TestCase
{
  public function testFactoryCreation()
  {
    $this->assertFalse(rex_test_factory::hasFactoryClass(), 'initially no factory class is set');
    $this->assertEquals('rex_test_factory', rex_test_factory::getFactoryClass(), 'original factory class will be returned');
    $clazz = rex_test_factory::factory();
    $this->assertEquals('rex_test_factory', $clazz, 'factory class defaults to the original impl');
    $obj = new $clazz();
    $this->assertEquals('base', $obj->doSomething(), 'call method of the original impl');
    $this->assertEquals('static-base', rex_test_factory::staticCall(), 'static method of original impl');

    rex_test_factory::setFactoryClass('rex_alternative_test_factory');
    $this->assertTrue(rex_test_factory::hasFactoryClass(), 'factory class was set');
    $this->assertEquals('rex_alternative_test_factory', rex_test_factory::getFactoryClass(), 'factory class will be returned');
    $clazz = rex_test_factory::factory();
    $this->assertEquals('rex_alternative_test_factory', $clazz, 'alternative factory class will be used');
    $obj = new $clazz();
    $this->assertEquals('overridden', $obj->doSomething(), 'call method of the alternative impl');
    $this->assertEquals('static-overridden', rex_test_factory::staticCall(), 'static method of alternative impl');
  }
}
