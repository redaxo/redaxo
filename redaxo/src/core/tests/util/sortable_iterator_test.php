<?php

class rex_sortable_iterator_test extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        parent::setUp();
    }

    public function tearDown()
    {
        parent::tearDown();
    }

    public function testValuesMode()
    {
        $array = [2, 'a10', 'a2', 1, "a\xec\x884", 'ä3', 'b'];
        $iterator = new rex_sortable_iterator(new ArrayIterator($array));
        $this->assertSame(
            [3 => 1, 0 => 2, 2 => 'a2', 5 => 'ä3', 4 => "a\xec\x884", 1 => 'a10', 6 => 'b'],
            iterator_to_array($iterator),
            'On default the iterator sorts by value'
        );
    }

    public function testKeysMode()
    {
        $array = [2 => 0, 'a' => 1, 1 => 2, 'b' => 3];
        $iterator = new rex_sortable_iterator(new ArrayIterator($array), rex_sortable_iterator::KEYS);
        $this->assertEquals(['a' => 1, 'b' => 3, 1 => 2, 2 => 0], iterator_to_array($iterator), 'In KEYS mode the iterator sorts by keys');
    }

    public function testCallbackMode()
    {
        $array = [2, 'a', 1, 'b'];
        $callback = function ($a, $b) {
            return strcmp($b, $a);
        };
        $iterator = new rex_sortable_iterator(new ArrayIterator($array), $callback);
        $this->assertEquals([0 => 2, 2 => 1, 3 => 'b', 1 => 'a'], iterator_to_array($iterator), 'If the secound parameter is a callback, the iterator sorts by using the function');
    }
}
