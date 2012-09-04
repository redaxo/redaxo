<?php

class rex_string_test extends PHPUnit_Framework_TestCase
{
  public function splitProvider()
  {
    return array(
      array('',                                          array()),
      array('a b c',                                     array('a', 'b', 'c')),
      array('"a b" cdef \'ghi kl\'',                     array('a b', 'cdef', 'ghi kl')),
      array('a=1 b=xyz c="hu hu" 123=\'he he\'',         array('a' => 1, 'b' => 'xyz', 'c' => 'hu hu', '123' => 'he he')),
      array('a="a \"b\" c" b=\'a \\\'b\\\'\' c="a\\\\"', array('a' => 'a "b" c', 'b' => "a 'b'", 'c' => "a\\")),
      array("\n a=1\n b='aa\nbb'\n c='a'\n ",            array('a' => '1', 'b' => "aa\nbb", 'c' => 'a'))
    );
  }

  /**
   * @dataProvider splitProvider
   */
  public function testSplit($string, $expectedArray)
  {
    $this->assertEquals($expectedArray, rex_string::split($string));
  }

  public function testSize()
  {
    $this->assertEquals(3, rex_string::size('aä'));
  }

  public function compareVersionsProvider()
  {
    return array(
      array('1',      '1',      '='),
      array('1.0',    '1.0',    '='),
      array('1',      '1.0',    '='),
      array('1.0 a1', '1.0.a1', '='),
      array('1.0a1',  '1.0.a1', '='),
      array('1.0 alpha 1', '1.0.a1', '='),

      array('1',      '2',        '<'),
      array('1',      '1.1',      '<'),
      array('1.0',    '1.1',      '<'),
      array('1.1',    '1.2',      '<'),
      array('1.2',    '1.10',     '<'),
      array('1.a1',   '1',        '<'),
      array('1.a1',   '1.0',      '<'),
      array('1.a1',   '1.a2',     '<'),
      array('1.a1',   '1.b1',     '<'),
      array('1.0.a1', '1',        '<'),
      array('1.0.a1', '1.0.0.0.', '<'),
      array('1.0a1',  '1.0',      '<'),
      array('1.0a1',  '1.0.1',    '<'),
      array('1.0a1',  '1.0a2',    '<'),
      array('1.0',    '1.1a1',    '<'),
      array('1.0.1',  '1.1a1',    '<')
    );
  }

  /**
   * @dataProvider compareVersionsProvider
   */
  public function testCompareVersions($version1, $version2, $comparator)
  {
    $this->assertTrue(rex_string::compareVersions($version1, $version2, $comparator));
  }
}
