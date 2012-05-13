<?php

class rex_type_test extends PHPUnit_Framework_TestCase
{
  public function castProvider()
  {
    $callback = function($var)
    {
      return $var . 'b';
    };

    $arrayVar = array('key1' => 1, 'key2' => '2', 'key4' => 'a');
    $arrayCasts = array(
      array('key1', 'string', 0),
      array('key2', 'int', 1),
      array('key3', 'string', -1),
      array('key4', $callback)
    );
    $arrayExpected = array('key1' => '1', 'key2' => 2, 'key3' => -1, 'key4' => 'ab');

    return array(
      array(1, 'string', '1'),
      array(1, 'bool', true),
      array('', 'array', array()),
      array(1, 'array', array(1)),
      array(array(1, '2'), 'array[int]', array(1, 2)),
      array('a', $callback, 'ab'),
      array($arrayVar, $arrayCasts, $arrayExpected),
      array(
        array('k' => $arrayVar),
        array(array('k', $arrayCasts)),
        array('k' => $arrayExpected)
      )
    );
  }

  /**
   * @dataProvider castProvider
   */
  public function testCast($var, $vartype, $expectedResult)
  {
    $this->assertSame($expectedResult, rex_type::cast($var, $vartype));
  }
}