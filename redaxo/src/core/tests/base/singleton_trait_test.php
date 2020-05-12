<?php

use PHPUnit\Framework\TestCase;

class rex_test_singleton
{
    use rex_singleton_trait;
}

/**
 * @internal
 */
class rex_singleton_trait_test extends TestCase
{
    public function testGetInstance()
    {
        static::assertInstanceOf('rex_test_singleton', rex_test_singleton::getInstance(), 'instance of the correct class is returned');
        static::assertEquals('rex_test_singleton', get_class(rex_test_singleton::getInstance()), 'excact class is returned');
        static::assertTrue(rex_test_singleton::getInstance() === rex_test_singleton::getInstance(), 'the very same instance is returned on every invocation');
    }

    public function testClone()
    {
        $this->expectException(\BadMethodCallException::class);

        $clone = clone rex_test_singleton::getInstance();
    }
}
