<?php
class rex_rex_test extends PHPUnit_Framework_TestCase
{
  public function testRexConfig()
  {
    $key = 'aTestKey';
    // initial test on empty config
    $this->assertFalse(rex::hasConfig($key), 'the key does not exists at first');
    $this->assertNull(rex::getConfig($key), 'getting non existing key returns null');
    $this->assertEquals(rex::getConfig($key, 'defVal'), 'defVal', 'getting non existing key returns a given default');
    $this->assertFalse(rex::removeConfig($key), 'remove non existing key returns false');
    
    // test after setting a value
    $this->assertFalse(rex::setConfig($key, 'aVal'), 'setting non-existant value returns false');
    $this->assertEquals(rex::getConfig($key, 'defVal'), 'aVal', 'getting existing key returns its value');
    $this->assertTrue(rex::hasConfig($key), 'setted value exists');
    
    // test after re-setting a value
    $this->assertTrue(rex::setConfig($key, 'aOtherVal'), 're-setting a value returns true');
    $this->assertEquals(rex::getConfig($key, 'defaOtherVal'), 'aOtherVal', 'getting existing key returns its value');
    
    // test after cleanup
    $this->assertTrue(rex::removeConfig($key), 'remove a existing key returns true');
    $this->assertFalse(rex::hasConfig($key), 'the key does not exists after removal');
    $this->assertNull(rex::getConfig($key), 'getting non existing key returns null');
    $this->assertEquals(rex::getConfig($key, 'defVal'), 'defVal', 'getting non existing key returns a given default');
  }
  
  public function testRexProperty()
  {
    $key = 'aTestKey';
    // initial test on empty config
    $this->assertFalse(rex::hasProperty($key), 'the key does not exists at first');
    $this->assertNull(rex::getProperty($key), 'getting non existing key returns null');
    $this->assertEquals(rex::getProperty($key, 'defVal'), 'defVal', 'getting non existing key returns a given default');
    $this->assertFalse(rex::removeProperty($key), 'remove non existing key returns false');
    
    // test after setting a value
    $this->assertFalse(rex::setProperty($key, 'aVal'), 'setting non-existant value returns false');
    $this->assertEquals(rex::getProperty($key, 'defVal'), 'aVal', 'getting existing key returns its value');
    $this->assertTrue(rex::hasProperty($key), 'setted value exists');
    
    // test after re-setting a value
    $this->assertTrue(rex::setProperty($key, 'aOtherVal'), 're-setting a value returns true');
    $this->assertEquals(rex::getProperty($key, 'defaOtherVal'), 'aOtherVal', 'getting existing key returns its value');
    
    // test after cleanup
    $this->assertTrue(rex::removeProperty($key), 'remove a existing key returns true');
    $this->assertFalse(rex::hasProperty($key), 'the key does not exists after removal');
    $this->assertNull(rex::getProperty($key), 'getting non existing key returns null');
    $this->assertEquals(rex::getProperty($key, 'defVal'), 'defVal', 'getting non existing key returns a given default');
  }
}