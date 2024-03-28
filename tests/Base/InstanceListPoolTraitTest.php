<?php

namespace Redaxo\Core\Tests\Base;

use PHPUnit\Framework\Attributes\Depends;
use PHPUnit\Framework\TestCase;
use Redaxo\Core\Base\InstanceListPoolTrait;

/** @internal */
final class TestInstanceListPool
{
    use InstanceListPoolTrait {
        addInstanceList as public;
        hasInstanceList as public;
        getInstanceList as public;
        getInstanceListPoolKey as public;
    }

    private function __construct(
        protected int $id,
    ) {}

    public static function get(int $id): self
    {
        return new self($id);
    }
}

/** @internal */
final class InstanceListPoolTraitTest extends TestCase
{
    public function testAddHasInstanceList(): void
    {
        self::assertFalse(TestInstanceListPool::hasInstanceList(1), 'hasInstanceList returns false for non-existing instance');
        TestInstanceListPool::addInstanceList(1, [2, 3]);
        self::assertTrue(TestInstanceListPool::hasInstanceList(1), 'hasInstanceList returns true for added instance');
    }

    public function testGetInstanceList(): void
    {
        self::assertSame([], TestInstanceListPool::getInstanceList(2, TestInstanceListPool::get(...)), 'getInstanceList returns empty array for non-existing key');

        $expected = [
            TestInstanceListPool::get(1),
            TestInstanceListPool::get(2),
        ];
        self::assertEquals($expected, TestInstanceListPool::getInstanceList(2, TestInstanceListPool::get(...), function ($id) {
            $this->assertEquals(2, $id);
            return [1, 2];
        }), 'getInstance returns array of instances');

        TestInstanceListPool::getInstanceList(2, TestInstanceListPool::get(...), function (): array {
            $this->fail('getInstanceList does not call $createListCallback if list alreays exists');
        });

        TestInstanceListPool::getInstanceList([3, 'test'], TestInstanceListPool::get(...), function ($key1, $key2): array {
            $this->assertEquals(3, $key1, 'getInstanceList passes key array as arguments to callback');
            $this->assertEquals('test', $key2, 'getInstanceList passes key array as arguments to callback');

            return [];
        });

        TestInstanceListPool::getInstanceList(
            4,
            function ($key1, $key2) {
                $this->assertEquals(3, $key1, 'getInstanceList passes instance key array as arguments to callback');
                $this->assertEquals('test', $key2, 'getInstanceList passes instance key array as arguments to callback');
            },
            static function () {
                return [[3, 'test']];
            },
        );
    }

    #[Depends('testGetInstanceList')]
    public function testClearInstanceList(): void
    {
        TestInstanceListPool::clearInstanceList(2);
        self::assertFalse(TestInstanceListPool::hasInstanceList(2), 'instance list is cleared after clearInstanceList()');
    }

    #[Depends('testClearInstanceList')]
    public function testClearInstanceListPool(): void
    {
        TestInstanceListPool::clearInstanceListPool();
        self::assertFalse(TestInstanceListPool::hasInstanceList(1), 'instance lists are cleared after clearInstanceListPool()');
    }

    public function testGetInstanceListPoolKey(): void
    {
        self::assertEquals('1', TestInstanceListPool::getInstanceListPoolKey(1));
        self::assertEquals('2###test', TestInstanceListPool::getInstanceListPoolKey([2, 'test']));
    }
}
