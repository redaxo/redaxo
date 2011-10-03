<?php

class rex_file_test extends PHPUnit_Framework_TestCase
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
    return rex_path::addonData('tests', 'rex_file_test/'. $file);
  }

  public function testGetDefault()
  {
    $file = $this->getPath('non_existing.txt');
    $this->assertNull(rex_file::get($file));
    $myDefault = 'myDefault';
    $this->assertEquals($myDefault, rex_file::get($file, $myDefault));
  }

  public function testGetConfigDefault()
  {
    $file = $this->getPath('non_existing.txt');
    $this->assertEquals(array(), rex_file::getConfig($file));
    $myDefault = array('myDefault');
    $this->assertEquals($myDefault, rex_file::getConfig($file, $myDefault));
  }

  public function testGetCacheDefault()
  {
    $file = $this->getPath('non_existing.txt');
    $this->assertEquals(array(), rex_file::getCache($file));
    $myDefault = array('myDefault');
    $this->assertEquals($myDefault, rex_file::getCache($file, $myDefault));
  }

  public function testPutGet()
  {
    $file = $this->getPath('putget.txt');
    $content = 'test';
    $this->assertTrue(rex_file::put($file, $content));
    $this->assertEquals($content, rex_file::get($file));
  }

  public function testPutGetConfig()
  {
    $file = $this->getPath('putgetcache.txt');
    $content = array('test', 'key' => 'value');
    $this->assertTrue(rex_file::putConfig($file, $content));
    $this->assertEquals($content, rex_file::getConfig($file));
  }

  public function testPutGetCache()
  {
    $file = $this->getPath('putgetcache.txt');
    $content = array('test', 'key' => 'value');
    $this->assertTrue(rex_file::putCache($file, $content));
    $this->assertEquals($content, rex_file::getCache($file));
  }

  public function testPutInNewDir()
  {
    $file = $this->getPath('subdir/test.txt');
    $content = 'test';
    $this->assertTrue(rex_file::put($file, $content));
    $this->assertEquals($content, rex_file::get($file));
  }

  public function testCopyToFile()
  {
    $orig = $this->getPath('orig.txt');
    $copy = $this->getPath('copy.txt');
    $content = 'test';
    rex_file::put($orig, $content);
    $this->assertTrue(rex_file::copy($orig, $copy));
    $this->assertEquals($content, rex_file::get($orig));
    $this->assertEquals($content, rex_file::get($copy));
  }

  public function testCopyToDir()
  {
    $orig = $this->getPath('file.txt');
    $copyDir = $this->getPath('copy');
    $copyFile = $this->getPath('copy/file.txt');
    $content = 'test';
    rex_file::put($orig, $content);
    rex_dir::create($copyDir);
    $this->assertTrue(rex_file::copy($orig, $copyDir));
    $this->assertEquals($content, rex_file::get($copyFile));
  }

  public function testDelete()
  {
    $file = $this->getPath('delete.txt');
    rex_file::put($file, '');
    $this->assertTrue(file_exists($file));
    $this->assertTrue(rex_file::delete($file));
    $this->assertFalse(file_exists($file));
    $this->assertTrue(rex_file::delete($file));
  }

  public function testExtension()
  {
    $this->assertEquals('txt', rex_file::extension('test.txt'));
    $this->assertEquals('txt', rex_file::extension('test.file.txt'));
  }
}