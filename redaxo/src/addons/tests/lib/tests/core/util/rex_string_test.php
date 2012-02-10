<?php

class rex_string_test extends PHPUnit_Framework_TestCase
{
  public function testSplit()
  {
    $s = 'a b c';
    $a = array('a', 'b', 'c');
    $this->assertEquals($a, rex_string::split($s), 'splits string by space');

    $s = '"a b" cdef \'ghi kl\'';
    $a = array('a b', 'cdef', 'ghi kl');
    $this->assertEquals($a, rex_string::split($s), 'supports quoted strings');

    $s = 'a=1 b=xyz c="hu hu" 123=\'he he\'';
    $a = array('a' => 1, 'b' => 'xyz', 'c' => 'hu hu', '123' => 'he he');
    $this->assertEquals($a, rex_string::split($s), 'supports key value pairs');

    $s = 'a="a \"b\" c" b=\'a \\\'b\\\'\' c="a\\\\"';
    $a = array('a' => 'a "b" c', 'b' => "a 'b'", 'c' => "a\\");
    $this->assertEquals($a, rex_string::split($s), 'supports escaped quotes');

    $s = '
      a=1
      b="aa
bb"
      c="a"
    ';
    $a = array('a' => '1', 'b' => "aa\nbb", 'c' => 'a');
    $this->assertEquals($a, rex_string::split($s), 'supports multilines');
  }

  public function testSize()
  {
    $this->assertEquals(3, rex_string::size('aä'));
  }

  public function testCompareVersions()
  {
    $this->assertTrue(rex_string::compareVersions('1', '1', '='), '1 is equal to 1');
    $this->assertTrue(rex_string::compareVersions('1.0', '1.0', '='), '1.0 is equal to 1.0');
    $this->assertTrue(rex_string::compareVersions('1', '1.0', '='), '1 is equal to 1.0');
    $this->assertTrue(rex_string::compareVersions('1.0 a1', '1.0.a1', '='), '1.0 a1 is equal to 1.0.a1');
    $this->assertTrue(rex_string::compareVersions('1.0a1', '1.0.a1', '='), '1.0a1 is equal to 1.0.a1');
    $this->assertTrue(rex_string::compareVersions('1.0 alpha 1', '1.0.a1', '='), '1.0 alpha 1 is equal to 1.0.a1');

    $this->assertTrue(rex_string::compareVersions('1', '2', '<'), '1 is less than 2');
    $this->assertTrue(rex_string::compareVersions('1', '1.1', '<'), '1 is less than 1.1');
    $this->assertTrue(rex_string::compareVersions('1.0', '1.1', '<'), '1.0 is less than 1.1');
    $this->assertTrue(rex_string::compareVersions('1.1', '1.2', '<'), '1.1 is less than 1.2');
    $this->assertTrue(rex_string::compareVersions('1.2', '1.10', '<'), '1.2 is less than 1.10');
    $this->assertTrue(rex_string::compareVersions('1.a1', '1', '<'), '1.a1 is less than 1');
    $this->assertTrue(rex_string::compareVersions('1.a1', '1.0', '<'), '1.a1 is less than 1.0');
    $this->assertTrue(rex_string::compareVersions('1.a1', '1.a2', '<'), '1.a1 is less than 1.a2');
    $this->assertTrue(rex_string::compareVersions('1.a1', '1.b1', '<'), '1.a1 is less than 1.b1');
    $this->assertTrue(rex_string::compareVersions('1.0.a1', '1', '<'), '1.0.a1 is less than 1');
    $this->assertTrue(rex_string::compareVersions('1.0.a1', '1.0.0.0', '<'), '1.0.a1 is less than 1.0.0.0');
    $this->assertTrue(rex_string::compareVersions('1.0a1', '1.0', '<'), '1.0a1 is less than 1.0');
    $this->assertTrue(rex_string::compareVersions('1.0a1', '1.0.1', '<'), '1.0a1 is less than 1.0.1');
    $this->assertTrue(rex_string::compareVersions('1.0a1', '1.0a2', '<'), '1.0a1 is less than 1.0a2');
    $this->assertTrue(rex_string::compareVersions('1.0', '1.1a1', '<'), '1.0 is less than 1.1a1');
    $this->assertTrue(rex_string::compareVersions('1.0.1', '1.1a1', '<'), '1.0.1 is less than 1.1a1');
  }
}
