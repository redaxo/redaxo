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
    public function testGetInstance(): void
    {
        self::assertInstanceOf(rex_test_singleton::class, rex_test_singleton::getInstance(), 'instance of the correct class is returned');
        self::assertEquals(rex_test_singleton::class, get_class(rex_test_singleton::getInstance()), 'excact class is returned');
        self::assertTrue(rex_test_singleton::getInstance() === rex_test_singleton::getInstance(), 'the very same instance is returned on every invocation');
    }

    public function testClone(): void
    {
        $this->expectException(BadMethodCallException::class);

        clone rex_test_singleton::getInstance();
    }
}
