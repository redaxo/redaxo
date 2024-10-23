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
        private int $id, // @phpstan-ignore property.onlyWritten
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
        self::assertEquals($expected, TestInstanceListPool::getInstanceList(2, TestInstanceListPool::get(...), static function () {
            return [1, 2];
        }), 'getInstance returns array of instances');

        TestInstanceListPool::getInstanceList(2, TestInstanceListPool::get(...), static function (): array {
            self::fail('getInstanceList does not call $createListCallback if list alreays exists');
        });

        /** @psalm-suppress InvalidArgument */
        TestInstanceListPool::getInstanceList(
            4,
            static function (array $keys): ?object {
                self::assertEquals(3, $keys[0], 'getInstanceList passes instance key array as arguments to callback');
                self::assertEquals('test', $keys[1], 'getInstanceList passes instance key array as arguments to callback');

                return null;
            },
            static function (): array {
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
