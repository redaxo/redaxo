<?php

class rex_func_other_test extends PHPUnit_Framework_TestCase
{
  public function setUp()
  {
    parent::setUp();
  }

  public function tearDown()
  {
    parent::tearDown();
  }

  public function testSplitString()
  {
    $s1 = 'a b c';
    $this->assertEquals(array('a', 'b', 'c'), rex_split_string($s1), 'splits string by space');

    $s1 = '"a b" cdef \'ghi kl\'';
    $this->assertEquals(array('a b', 'cdef', 'ghi kl'), rex_split_string($s1), 'supports quoted strings');

    $s1 = 'a=1 b=xyz c="hu hu" 123=\'he he\'';
    $this->assertEquals(array('a' => 1, 'b' => 'xyz', 'c' => 'hu hu', '123' => 'he he'), rex_split_string($s1), 'supports key value pairs');
  }

  public function testVersionCompare()
  {
    $this->assertTrue(rex_version_compare('1', '1', '='), '1 is equal to 1');
    $this->assertTrue(rex_version_compare('1.0', '1.0', '='), '1.0 is equal to 1.0');
    $this->assertTrue(rex_version_compare('1', '1.0', '='), '1 is equal to 1.0');
    $this->assertTrue(rex_version_compare('1.0 a1', '1.0.a1', '='), '1.0 a1 is equal to 1.0.a1');
    $this->assertTrue(rex_version_compare('1.0a1', '1.0.a1', '='), '1.0a1 is equal to 1.0.a1');
    $this->assertTrue(rex_version_compare('1.0 alpha 1', '1.0.a1', '='), '1.0 alpha 1 is equal to 1.0.a1');

    $this->assertTrue(rex_version_compare('1', '2', '<'), '1 is less than 2');
    $this->assertTrue(rex_version_compare('1', '1.1', '<'), '1 is less than 1.1');
    $this->assertTrue(rex_version_compare('1.0', '1.1', '<'), '1.0 is less than 1.1');
    $this->assertTrue(rex_version_compare('1.1', '1.2', '<'), '1.1 is less than 1.2');
    $this->assertTrue(rex_version_compare('1.2', '1.10', '<'), '1.2 is less than 1.10');
    $this->assertTrue(rex_version_compare('1.a1', '1', '<'), '1.a1 is less than 1');
    $this->assertTrue(rex_version_compare('1.a1', '1.0', '<'), '1.a1 is less than 1.0');
    $this->assertTrue(rex_version_compare('1.a1', '1.a2', '<'), '1.a1 is less than 1.a2');
    $this->assertTrue(rex_version_compare('1.a1', '1.b1', '<'), '1.a1 is less than 1.b1');
    $this->assertTrue(rex_version_compare('1.0.a1', '1', '<'), '1.0.a1 is less than 1');
    $this->assertTrue(rex_version_compare('1.0.a1', '1.0.0.0', '<'), '1.0.a1 is less than 1.0.0.0');
    $this->assertTrue(rex_version_compare('1.0a1', '1.0', '<'), '1.0a1 is less than 1.0');
    $this->assertTrue(rex_version_compare('1.0a1', '1.0.1', '<'), '1.0a1 is less than 1.0.1');
    $this->assertTrue(rex_version_compare('1.0a1', '1.0a2', '<'), '1.0a1 is less than 1.0a2');
    $this->assertTrue(rex_version_compare('1.0', '1.1a1', '<'), '1.0 is less than 1.1a1');
    $this->assertTrue(rex_version_compare('1.0.1', '1.1a1', '<'), '1.0.1 is less than 1.1a1');
  }
}