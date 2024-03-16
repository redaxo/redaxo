<?php

use PHPUnit\Framework\Attributes\Depends;
use PHPUnit\Framework\TestCase;

/** @internal */
final class rex_test_instance_list_pool
{
    use rex_instance_list_pool_trait {
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
final class rex_instance_list_pool_trait_test extends TestCase
{
    public function testAddHasInstanceList(): void
    {
        self::assertFalse(rex_test_instance_list_pool::hasInstanceList(1), 'hasInstanceList returns false for non-existing instance');
        rex_test_instance_list_pool::addInstanceList(1, [2, 3]);
        self::assertTrue(rex_test_instance_list_pool::hasInstanceList(1), 'hasInstanceList returns true for added instance');
    }

    public function testGetInstanceList(): void
    {
        self::assertSame([], rex_test_instance_list_pool::getInstanceList(2, rex_test_instance_list_pool::get(...)), 'getInstanceList returns empty array for non-existing key');

        $expected = [
            rex_test_instance_list_pool::get(1),
            rex_test_instance_list_pool::get(2),
        ];
        self::assertEquals($expected, rex_test_instance_list_pool::getInstanceList(2, rex_test_instance_list_pool::get(...), function ($id) {
            $this->assertEquals(2, $id);
            return [1, 2];
        }), 'getInstance returns array of instances');

        rex_test_instance_list_pool::getInstanceList(2, rex_test_instance_list_pool::get(...), function (): array {
            $this->fail('getInstanceList does not call $createListCallback if list alreays exists');
        });

        rex_test_instance_list_pool::getInstanceList([3, 'test'], rex_test_instance_list_pool::get(...), function ($key1, $key2): array {
            $this->assertEquals(3, $key1, 'getInstanceList passes key array as arguments to callback');
            $this->assertEquals('test', $key2, 'getInstanceList passes key array as arguments to callback');

            return [];
        });

        rex_test_instance_list_pool::getInstanceList(
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
        rex_test_instance_list_pool::clearInstanceList(2);
        self::assertFalse(rex_test_instance_list_pool::hasInstanceList(2), 'instance list is cleared after clearInstanceList()');
    }

    #[Depends('testClearInstanceList')]
    public function testClearInstanceListPool(): void
    {
        rex_test_instance_list_pool::clearInstanceListPool();
        self::assertFalse(rex_test_instance_list_pool::hasInstanceList(1), 'instance lists are cleared after clearInstanceListPool()');
    }

    public function testGetInstanceListPoolKey(): void
    {
        self::assertEquals('1', rex_test_instance_list_pool::getInstanceListPoolKey(1));
        self::assertEquals('2###test', rex_test_instance_list_pool::getInstanceListPoolKey([2, 'test']));
    }
}
