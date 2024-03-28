<?php

namespace Redaxo\Core\Tests\Base;

use BadMethodCallException;
use PHPUnit\Framework\TestCase;
use Redaxo\Core\Base\SingletonTrait;

use function get_class;

/** @internal */
final class TestSingleton
{
    use SingletonTrait;
}

/** @internal */
final class SingletonTraitTest extends TestCase
{
    public function testGetInstance(): void
    {
        self::assertInstanceOf(TestSingleton::class, TestSingleton::getInstance(), 'instance of the correct class is returned');
        self::assertEquals(TestSingleton::class, get_class(TestSingleton::getInstance()), 'excact class is returned');
        self::assertTrue(TestSingleton::getInstance() === TestSingleton::getInstance(), 'the very same instance is returned on every invocation');
    }

    public function testClone(): void
    {
        $this->expectException(BadMethodCallException::class);

        clone TestSingleton::getInstance();
    }
}
