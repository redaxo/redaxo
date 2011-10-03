<?php

class rex_dir_test extends PHPUnit_Framework_TestCase
{
  public function setUp()
  {
    parent::setUp();

    rex_dir::create($this->getPath());
  }

  public function tearDown()
  {
    parent::tearDown();

    rex_dir::delete($this->getPath());
  }

  private function getPath($file = '')
  {
    return rex_path::addonData('tests', 'rex_dir_test/'. $file);
  }

  public function testCreate()
  {
    $path = $this->getPath('create');
    $this->assertTrue(rex_dir::create($path));
    $this->assertTrue(is_dir($path));
    $this->assertTrue(rex_dir::create($path));
  }

  public function testCreateRecursive()
  {
    $path = $this->getPath('create_recursive/test/test');
    $this->assertTrue(rex_dir::create($path));
    $this->assertTrue(is_dir($path));
  }

  public function testCopyToNewDir()
  {
    $orig = $this->getPath('orig1');
    $copy = $this->getPath('copy1');
    rex_dir::create($orig .'/dir1');
    rex_file::put($orig .'/file.txt', '');
    rex_file::put($orig .'/dir2/file.txt', '');

    $this->assertTrue(rex_dir::copy($orig, $copy));
    $this->assertTrue(is_dir($copy .'/dir1'));
    $this->assertTrue(is_file($copy .'/file.txt'));
    $this->assertTrue(is_file($copy .'/dir2/file.txt'));
  }

  public function testCopyToExistingDir()
  {
    $orig = $this->getPath('orig2');
    $copy = $this->getPath('copy2');
    // dir1 only in /orig
    rex_dir::create($orig .'/dir1');
    // dir2 only in /copy
    rex_dir::create($copy .'/dir2');
    // file1 only in /orig
    rex_file::put($orig .'/file1.txt', '');
    rex_file::put($orig .'/dir3/file1.txt', '');
    // file2 with newest version in /orig
    rex_file::put($copy .'/file2.txt', 'file2_old');
    touch($copy .'/file2.txt', 1);
    rex_file::put($copy .'/dir3/file2.txt', 'file2_old');
    touch($copy .'/dir3/file2.txt', 1);
    rex_file::put($orig .'/file2.txt', 'file2_new');
    rex_file::put($orig .'/dir3/file2.txt', 'file2_new');
    // file3 with newest version /copy
    rex_file::put($orig .'/file3.txt', 'file3_old');
    touch($orig .'/file3.txt', 1);
    rex_file::put($orig .'/dir3/file3.txt', 'file3_old');
    touch($orig .'/dir3/file3.txt', 1);
    rex_file::put($copy .'/file3.txt', 'file3_new');
    rex_file::put($copy .'/dir3/file3.txt', 'file3_new');

    $this->assertTrue(rex_dir::copy($orig, $copy));
    $this->assertTrue(is_dir($copy .'/dir1'));
    $this->assertTrue(is_dir($copy .'/dir2'));
    $this->assertTrue(is_file($copy .'/file1.txt'));
    $this->assertTrue(is_file($copy .'/dir3/file1.txt'));
    $this->assertEquals('file2_new', rex_file::get($copy .'/file2.txt'));
    $this->assertEquals('file2_new', rex_file::get($copy .'/dir3/file2.txt'));
    $this->assertEquals('file3_new', rex_file::get($copy .'/file3.txt'));
    $this->assertEquals('file3_new', rex_file::get($copy .'/dir3/file3.txt'));
  }

  public function testDeleteComplete()
  {
    $dir = $this->getPath('deleteComplete');
    $file = $this->getPath('deleteComplete/subdir/file.txt');
    rex_file::put($file, '');

    $this->assertTrue(is_file($file));
    $this->assertTrue(rex_dir::delete($dir));
    $this->assertFalse(is_dir($dir));
  }

  public function testDeleteWithoutSelf()
  {
    $dir = $this->getPath('deleteCompleteWithoutSelf');
    $file = $this->getPath('deleteCompleteWithoutSelf/subdir/file.txt');
    rex_file::put($file, '');

    $this->assertTrue(is_file($file));
    $this->assertTrue(rex_dir::delete($dir, false));
    $this->assertFalse(is_file($file));
    $this->assertFalse(is_dir($dir .'/subdir'));
    $this->assertTrue(is_dir($dir));
  }

  public function testDeleteFilesNotRecursive()
  {
    $dir = $this->getPath('deleteFilesNotRecursive');
    $file1 = $this->getPath('deleteFilesNotRecursive/file.txt');
    $file2 = $this->getPath('deleteFilesNotRecursive/subdir/file.txt');
    rex_file::put($file1, '');
    rex_file::put($file2, '');

    $this->assertTrue(is_file($file1));
    $this->assertTrue(is_file($file2));
    $this->assertTrue(rex_dir::deleteFiles($dir, false));
    $this->assertFalse(is_file($file1));
    $this->assertTrue(is_file($file2));
  }

  public function testDeleteFilesRecursive()
  {
    $dir = $this->getPath('deleteFilesRecursive');
    $file1 = $this->getPath('deleteFilesRecursive/file.txt');
    $file2 = $this->getPath('deleteFilesRecursive/subdir/file.txt');
    rex_file::put($file1, '');
    rex_file::put($file2, '');

    $this->assertTrue(is_file($file1));
    $this->assertTrue(is_file($file2));
    $this->assertTrue(rex_dir::deleteFiles($dir));
    $this->assertFalse(is_file($file1));
    $this->assertFalse(is_file($file2));
    $this->assertTrue(is_dir($dir .'/subdir'));
  }
}