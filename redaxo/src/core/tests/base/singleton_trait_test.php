<?php

class rex_test_singleton
{
    use rex_singleton_trait;
}

class rex_singleton_trait_test extends PHPUnit_Framework_TestCase
{
    public function testGetInstance()
    {
        $this->assertInstanceOf('rex_test_singleton', rex_test_singleton::getInstance(), 'instance of the correct class is returned');
        $this->assertEquals('rex_test_singleton', get_class(rex_test_singleton::getInstance()), 'excact class is returned');
        $this->assertTrue(rex_test_singleton::getInstance() === rex_test_singleton::getInstance(), 'the very same instance is returned on every invocation');
    }

    /**
     * @expectedException BadMethodCallException
     */
    public function testClone()
    {
        $clone = clone rex_test_singleton::getInstance();
    }
}
