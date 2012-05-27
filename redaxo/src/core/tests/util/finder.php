<?php

class rex_finder_test extends PHPUnit_Framework_TestCase
{
  public function setUp()
  {
    parent::setUp();

    rex_file::put($this->getPath('file1.txt'), '');
    rex_file::put($this->getPath('file2.txt'), '');
    rex_file::put($this->getPath('dir1/file3.txt'), '');
    rex_file::put($this->getPath('dir2/file4.yml'), '');
    rex_file::put($this->getPath('dir2/dir3/file5.xxx'), '');
    rex_dir::create($this->getPath('dir1/dir'));
    rex_dir::create($this->getPath('dir3'));
  }

  public function tearDown()
  {
    parent::tearDown();

    rex_dir::delete($this->getPath());
  }

  public function getPath($file = '')
  {
    return rex_path::addonData('tests', 'rex_dir_iterator_test/'. $file);
  }

  protected function assertContainsPath(array $array, $relPath)
  {
    $this->assertArrayHasKey($this->getPath($relPath), $array, $relPath. ' is in array');
  }

  public function testDefault()
  {
    $finder = rex_finder::factory($this->getPath());
    $array = iterator_to_array($finder, true);
    $this->assertEquals(5, count($finder), 'default iterator returns all elements of first level');

    $this->assertContainsPath($array, 'file1.txt');
    $this->assertContainsPath($array, 'file2.txt');
    $this->assertContainsPath($array, 'dir1');
    $this->assertContainsPath($array, 'dir2');
    $this->assertContainsPath($array, 'dir3');
  }

  public function testRecursive()
  {
    $finder = rex_finder::factory($this->getPath())->recursive();
    $array = iterator_to_array($finder, true);

    $this->assertEquals(10, count($finder), 'recursive iterator returns all elements of all levels');

    $this->assertContainsPath($array, 'file1.txt');
    $this->assertContainsPath($array, 'file2.txt');
    $this->assertContainsPath($array, 'dir1');
    $this->assertContainsPath($array, 'dir1/dir');
    $this->assertContainsPath($array, 'dir1/file3.txt');
    $this->assertContainsPath($array, 'dir2');
    $this->assertContainsPath($array, 'dir2/dir3');
    $this->assertContainsPath($array, 'dir2/file4.yml');
    $this->assertContainsPath($array, 'dir2/dir3/file5.xxx');
    $this->assertContainsPath($array, 'dir3');
  }

  public function testFilterTxtFiles()
  {
    $finder = rex_finder::factory($this->getPath())->recursive()->filterFiles('*.txt');
    $array = iterator_to_array($finder, true);

    $this->assertEquals(8, count($finder), 'recursive iterator returns all elements of all levels filtered by filepattern, leave folders untouched');

    $this->assertContainsPath($array, 'file1.txt');
    $this->assertContainsPath($array, 'file2.txt');
    $this->assertContainsPath($array, 'dir1');
    $this->assertContainsPath($array, 'dir1/dir');
    $this->assertContainsPath($array, 'dir1/file3.txt');
    $this->assertContainsPath($array, 'dir2');
    $this->assertContainsPath($array, 'dir2/dir3');
    $this->assertContainsPath($array, 'dir3');
  }

  public function testIgnoreTxtFiles()
  {
    $finder = rex_finder::factory($this->getPath())->recursive()->ignoreFiles('*.txt');
    $array = iterator_to_array($finder, true);

    $this->assertEquals(7, count($finder), 'recursive iterator returns all elements of all levels but ignores a filepattern, leave folders untouched');

    $this->assertContainsPath($array, 'dir1');
    $this->assertContainsPath($array, 'dir1/dir');
    $this->assertContainsPath($array, 'dir2');
    $this->assertContainsPath($array, 'dir2/dir3');
    $this->assertContainsPath($array, 'dir2/file4.yml');
    $this->assertContainsPath($array, 'dir2/dir3/file5.xxx');
    $this->assertContainsPath($array, 'dir3');
  }

  public function testFilterDirs()
  {
    $finder = rex_finder::factory($this->getPath())->recursive()->filterDirs('dir3');
    $array = iterator_to_array($finder, true);

    $this->assertEquals(7, count($finder), 'recursive iterator returns all elements of all levels filtered by dirpattern');

    $this->assertContainsPath($array, 'file1.txt');
    $this->assertContainsPath($array, 'file2.txt');
    $this->assertContainsPath($array, 'dir1/file3.txt');
    $this->assertContainsPath($array, 'dir2/dir3');
    $this->assertContainsPath($array, 'dir2/file4.yml');
    $this->assertContainsPath($array, 'dir2/dir3/file5.xxx');
    $this->assertContainsPath($array, 'dir3');
  }

  public function testIgnoreDirs()
  {
    $finder = rex_finder::factory($this->getPath())->recursive()->ignoreDirs('dir3');
    $array = iterator_to_array($finder, true);

    $this->assertEquals(8, count($finder), 'recursive iterator returns all elements of all levels but ignores a dirpattern');

    $this->assertContainsPath($array, 'file1.txt');
    $this->assertContainsPath($array, 'file2.txt');
    $this->assertContainsPath($array, 'dir1');
    $this->assertContainsPath($array, 'dir1/dir');
    $this->assertContainsPath($array, 'dir1/file3.txt');
    $this->assertContainsPath($array, 'dir2');
    $this->assertContainsPath($array, 'dir2/file4.yml');
    $this->assertContainsPath($array, 'dir2/dir3/file5.xxx');

  }
  public function testFilterFilesIgnoreDirs()
  {
    $finder = rex_finder::factory($this->getPath())->recursive()->ignoreFiles('xxx')->ignoreDirs('*3');
    $array = iterator_to_array($finder, true);

    $this->assertEquals(8, count($finder), 'recursive iterator returns all elements of all levels but ignores a filepattern and dirs');

    $this->assertContainsPath($array, 'file1.txt');
    $this->assertContainsPath($array, 'file2.txt');
    $this->assertContainsPath($array, 'dir1');
    $this->assertContainsPath($array, 'dir1/dir');
    $this->assertContainsPath($array, 'dir1/file3.txt');
    $this->assertContainsPath($array, 'dir2');
    $this->assertContainsPath($array, 'dir2/file4.yml');
    $this->assertContainsPath($array, 'dir2/dir3/file5.xxx');
  }

//   public function testIgnoreAllDirs()
//   {
//     $iterator = rex_dir::iterator($this->getPath())->ignoreDirs();
//     $array = iterator_to_array($iterator, true);
//     $this->assertEquals(2, count($array), 'ignoreDirs() returns only files');
//     $this->assertArrayHasKey($this->getPath('file1.txt'), $array, 'file1 is in array');
//     $this->assertArrayHasKey($this->getPath('file2.txt'), $array, 'file2 is in array');
//   }

//   public function testIgnoreSpecificDirs()
//   {

//     $iterator = rex_dir::iterator($this->getPath())->ignoreDirs(array('dir1'));
//     $array = iterator_to_array($iterator, true);
//     $this->assertEquals(3, count($array), 'ignoreDir() with array ignores specific dirs');
//     $this->assertArrayHasKey($this->getPath('file1.txt'), $array, 'file1 is in array');
//     $this->assertArrayHasKey($this->getPath('file2.txt'), $array, 'file2 is in array');
//     $this->assertArrayHasKey($this->getPath('dir2'), $array, 'dir2 is in array');
//   }

//   public function testIgnoreAllFiles()
//   {
//     $iterator = rex_dir::iterator($this->getPath())->ignoreFiles();
//     $array = iterator_to_array($iterator, true);
//     $this->assertEquals(2, count($array), 'ignoreFiles() returns only dirs');
//     $this->assertArrayHasKey($this->getPath('dir1'), $array, 'dir1 is in array');
//     $this->assertArrayHasKey($this->getPath('dir2'), $array, 'dir2 is in array');
//   }

//   public function testIgnoreSpecificFiles()
//   {
//     $iterator = rex_dir::iterator($this->getPath())->ignoreFiles(array('file1.txt'));
//     $array = iterator_to_array($iterator, true);
//     $this->assertEquals(3, count($array), 'ignoreFiles() with array ignores specific files');
//     $this->assertArrayHasKey($this->getPath('file2.txt'), $array, 'file2 is in array');
//     $this->assertArrayHasKey($this->getPath('dir1'), $array, 'dir1 is in array');
//     $this->assertArrayHasKey($this->getPath('dir2'), $array, 'dir2 is in array');
//   }

//   public function testIgnorePrefixes()
//   {
//     $iterator = rex_dir::iterator($this->getPath())->ignorePrefixes(array('file1', 'dir1'));
//     $array = iterator_to_array($iterator, true);
//     $this->assertEquals(2, count($array), 'ignorePrefixes() ignores files and dirs with the given prefixes');
//     $this->assertArrayHasKey($this->getPath('file2.txt'), $array, 'file2 is in array');
//     $this->assertArrayHasKey($this->getPath('dir2'), $array, 'dir2 is in array');
//   }

//   public function testIgnoreSuffixes()
//   {
//     $iterator = rex_dir::iterator($this->getPath())->ignoreSuffixes(array('1.txt', '1'));
//     $array = iterator_to_array($iterator, true);
//     $this->assertEquals(2, count($array), 'ignoreSuffixes ignores files and dirs with the given suffixes');
//     $this->assertArrayHasKey($this->getPath('file2.txt'), $array, 'file2 is in array');
//     $this->assertArrayHasKey($this->getPath('dir2'), $array, 'dir2 is in array');
//   }
}

