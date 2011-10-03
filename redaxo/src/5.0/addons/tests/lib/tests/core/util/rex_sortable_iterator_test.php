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
    $array = array(2, 'a', 1, 'b');
    $iterator = new rex_sortable_iterator(new ArrayIterator($array));
    $this->assertEquals(array(1 => 'a', 3 => 'b', 2 => 1, 0 => 2), iterator_to_array($iterator));
  }

  public function testKeysMode()
  {
    $array = array(2 => 0, 'a' => 1, 1 => 2, 'b' => 3);
    $iterator = new rex_sortable_iterator(new ArrayIterator($array), rex_sortable_iterator::KEYS);
    $this->assertEquals(array('a' => 1, 'b' => 3, 1 => 2, 2 => 0), iterator_to_array($iterator));
  }

  public function testCallbackMode()
  {
    $array = array(2, 'a', 1, 'b');
    $callback = function($a, $b)
    {
      return strcmp($b, $a);
    };
    $iterator = new rex_sortable_iterator(new ArrayIterator($array), $callback);
    $this->assertEquals(array(0 => 2, 2 => 1, 3 => 'b', 1 => 'a'), iterator_to_array($iterator));
  }
}