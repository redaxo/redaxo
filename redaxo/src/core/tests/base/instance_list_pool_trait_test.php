<?php

use PHPUnit\Framework\TestCase;

class rex_test_instance_list_pool
{
    use rex_instance_list_pool_trait {
        addInstanceList as public;
        hasInstanceList as public;
        getInstanceList as public;
        getInstanceListPoolKey as public;
    }

    private $id;

    public static function get($id)
    {
        $instance = new self();
        $instance->id = $id;
        return $instance;
    }
}

/**
 * @internal
 */
class rex_instance_list_pool_trait_test extends TestCase
{
    public function testAddHasInstanceList()
    {
        static::assertFalse(rex_test_instance_list_pool::hasInstanceList(1), 'hasInstanceList returns false for non-existing instance');
        rex_test_instance_list_pool::addInstanceList(1, [2, 3]);
        static::assertTrue(rex_test_instance_list_pool::hasInstanceList(1), 'hasInstanceList returns true for added instance');
    }

    public function testGetInstanceList()
    {
        static::assertSame([], rex_test_instance_list_pool::getInstanceList(2, ['rex_test_instance_list_pool', 'get']), 'getInstanceList returns empty array for non-existing key');

        $expected = [
            rex_test_instance_list_pool::get(1),
            rex_test_instance_list_pool::get(2),
        ];
        static::assertEquals($expected, rex_test_instance_list_pool::getInstanceList(2, ['rex_test_instance_list_pool', 'get'], function ($id) {
            $this->assertEquals(2, $id);
            return [1, 2];
        }), 'getInstance returns array of instances');

        rex_test_instance_list_pool::getInstanceList(2, ['rex_test_instance_list_pool', 'get'], function () {
            $this->fail('getInstanceList does not call $createListCallback if list alreays exists');
        });

        rex_test_instance_list_pool::getInstanceList([3, 'test'], ['rex_test_instance_list_pool', 'get'], function ($key1, $key2) {
            $this->assertEquals(3, $key1, 'getInstanceList passes key array as arguments to callback');
            $this->assertEquals('test', $key2, 'getInstanceList passes key array as arguments to callback');
        });

        rex_test_instance_list_pool::getInstanceList(
            4,
            function ($key1, $key2) {
                $this->assertEquals(3, $key1, 'getInstanceList passes instance key array as arguments to callback');
                $this->assertEquals('test', $key2, 'getInstanceList passes instance key array as arguments to callback');
            },
            static function () {
                return [[3, 'test']];
            }
        );
    }

    /**
     * @depends testGetInstanceList
     */
    public function testClearInstanceList()
    {
        rex_test_instance_list_pool::clearInstanceList(2);
        static::assertFalse(rex_test_instance_list_pool::hasInstanceList(2), 'instance list is cleared after clearInstanceList()');
    }

    /**
     * @depends testClearInstanceList
     */
    public function testClearInstanceListPool()
    {
        rex_test_instance_list_pool::clearInstanceListPool();
        static::assertFalse(rex_test_instance_list_pool::hasInstanceList(1), 'instance lists are cleared after clearInstanceListPool()');
    }

    public function testGetInstanceListPoolKey()
    {
        static::assertEquals('1', rex_test_instance_list_pool::getInstanceListPoolKey(1));
        static::assertEquals('2###test', rex_test_instance_list_pool::getInstanceListPoolKey([2, 'test']));
    }
}
