<?php

class testSingleton extends rex_singleton
{}

class rex_singleton_test extends PHPUnit_Framework_TestCase
{
  public function testGetInstance()
  {
    $this->assertInstanceOf('testSingleton', testSingleton::getInstance(), 'instance of the correct class is returned');
    $this->assertEquals('testSingleton', get_class(testSingleton::getInstance()), 'excact class is returned');
    $this->assertTrue(testSingleton::getInstance() === testSingleton::getInstance(), 'the very same instance is returned on every invocation');
  } 
}
