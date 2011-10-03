<?php

class rex_dir_recursive_iterator_test extends PHPUnit_Framework_TestCase
{
  public function setUp()
  {
    parent::setUp();

    rex_file::put($this->getPath('file1.txt'), '');
    rex_file::put($this->getPath('file2.txt'), '');
    rex_file::put($this->getPath('dir1/file1.txt'), '');
    rex_file::put($this->getPath('dir1/file2.txt'), '');
    rex_dir::create($this->getPath('dir1/dir1'));
    rex_dir::create($this->getPath('dir1/dir2'));
    rex_dir::create($this->getPath('dir2'));
  }

  public function tearDown()
  {
    parent::tearDown();

    rex_dir::delete($this->getPath());
  }

  public function getPath($file = '')
  {
    return rex_path::addonData('tests', 'rex_dir_recursive_iterator_test/'. $file);
  }

  public function testDefault()
  {
    $iterator = rex_dir::recursiveIterator($this->getPath());
    $array = iterator_to_array($iterator, true);
    $this->assertEquals(8, count($array));
    $this->assertArrayHasKey($this->getPath('file1.txt'), $array);
    $this->assertArrayHasKey($this->getPath('file2.txt'), $array);
    $this->assertArrayHasKey($this->getPath('dir1'), $array);
    $this->assertArrayHasKey($this->getPath('dir1/file1.txt'), $array);
    $this->assertArrayHasKey($this->getPath('dir1/file2.txt'), $array);
    $this->assertArrayHasKey($this->getPath('dir1/dir1'), $array);
    $this->assertArrayHasKey($this->getPath('dir1/dir2'), $array);
    $this->assertArrayHasKey($this->getPath('dir2'), $array);
  }

  public function testExcludeAllDirs()
  {
    $iterator = rex_dir::recursiveIterator($this->getPath())->excludeDirs();
    $array = iterator_to_array($iterator, true);
    $this->assertEquals(2, count($array));
    $this->assertArrayHasKey($this->getPath('file1.txt'), $array);
    $this->assertArrayHasKey($this->getPath('file2.txt'), $array);
  }

  public function testExcludeSpecificDirs()
  {

    $iterator = rex_dir::recursiveIterator($this->getPath())->excludeDirs(array('dir1'));
    $array = iterator_to_array($iterator, true);
    $this->assertEquals(3, count($array));
    $this->assertArrayHasKey($this->getPath('file1.txt'), $array);
    $this->assertArrayHasKey($this->getPath('file2.txt'), $array);
    $this->assertArrayHasKey($this->getPath('dir2'), $array);
  }

  public function testExcludeAllFiles()
  {
    $iterator = rex_dir::recursiveIterator($this->getPath())->excludeFiles();
    $array = iterator_to_array($iterator, true);
    $this->assertEquals(4, count($array));
    $this->assertArrayHasKey($this->getPath('dir1'), $array);
    $this->assertArrayHasKey($this->getPath('dir1/dir1'), $array);
    $this->assertArrayHasKey($this->getPath('dir1/dir2'), $array);
    $this->assertArrayHasKey($this->getPath('dir2'), $array);
  }

  public function testExcludeSpecificFiles()
  {
    $iterator = rex_dir::recursiveIterator($this->getPath())->excludeFiles(array('file1.txt'));
    $array = iterator_to_array($iterator, true);
    $this->assertEquals(6, count($array));
    $this->assertArrayHasKey($this->getPath('file2.txt'), $array);
    $this->assertArrayHasKey($this->getPath('dir1'), $array);
    $this->assertArrayHasKey($this->getPath('dir1/file2.txt'), $array);
    $this->assertArrayHasKey($this->getPath('dir1/dir1'), $array);
    $this->assertArrayHasKey($this->getPath('dir1/dir2'), $array);
    $this->assertArrayHasKey($this->getPath('dir2'), $array);
  }

  public function testExcludePrefixes()
  {
    $iterator = rex_dir::recursiveIterator($this->getPath())->excludePrefixes(array('file2', 'dir2'));
    $array = iterator_to_array($iterator, true);
    $this->assertEquals(4, count($array));
    $this->assertArrayHasKey($this->getPath('file1.txt'), $array);
    $this->assertArrayHasKey($this->getPath('dir1'), $array);
    $this->assertArrayHasKey($this->getPath('dir1/file1.txt'), $array);
    $this->assertArrayHasKey($this->getPath('dir1/dir1'), $array);
  }

  public function testExcludeSuffixes()
  {
    $iterator = rex_dir::recursiveIterator($this->getPath())->excludeSuffixes(array('2.txt', '2'));
    $array = iterator_to_array($iterator, true);
    $this->assertEquals(4, count($array));
    $this->assertArrayHasKey($this->getPath('file1.txt'), $array);
    $this->assertArrayHasKey($this->getPath('dir1'), $array);
    $this->assertArrayHasKey($this->getPath('dir1/file1.txt'), $array);
    $this->assertArrayHasKey($this->getPath('dir1/dir1'), $array);
  }
}