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
        return rex_path::addonData('tests', 'rex_file_test/' . $file);
    }

    public function testGetDefault()
    {
        $file = $this->getPath('non_existing.txt');
        $this->assertNull(rex_file::get($file), 'get() returns null for non-existing files');
        $myDefault = 'myDefault';
        $this->assertEquals($myDefault, rex_file::get($file, $myDefault), 'get() returns given default value for non-existing files');
    }

    public function testGetConfigDefault()
    {
        $file = $this->getPath('non_existing.txt');
        $this->assertEquals(array(), rex_file::getConfig($file), 'getConfig() returns empty array for non-existing files');
        $myDefault = array('myDefault');
        $this->assertEquals($myDefault, rex_file::getConfig($file, $myDefault), 'getConfig() returns given default value for non-existing files');
    }

    public function testGetCacheDefault()
    {
        $file = $this->getPath('non_existing.txt');
        $this->assertEquals(array(), rex_file::getCache($file), 'getCache() returns empty array for non-existing files');
        $myDefault = array('myDefault');
        $this->assertEquals($myDefault, rex_file::getCache($file, $myDefault), 'getCache() returns given default value for non-existing files');
    }

    public function testPutGet()
    {
        $file = $this->getPath('putget.txt');
        $content = 'test';
        $this->assertTrue(rex_file::put($file, $content), 'put() returns true on success');
        $this->assertEquals($content, rex_file::get($file), 'get() returns content of file');
    }

    public function testPutGetConfig()
    {
        $file = $this->getPath('putgetcache.txt');
        $content = array('test', 'key' => 'value');
        $this->assertTrue(rex_file::putConfig($file, $content), 'putConfig() returns true on success');
        $this->assertEquals($content, rex_file::getConfig($file), 'getConfig() returns content of file');
    }

    public function testPutGetCache()
    {
        $file = $this->getPath('putgetcache.txt');
        $content = array('test', 'key' => 'value');
        $this->assertTrue(rex_file::putCache($file, $content), 'putCache() returns true on success');
        $this->assertEquals($content, rex_file::getCache($file), 'getCache() returns content of file');
    }

    public function testPutInNewDir()
    {
        $file = $this->getPath('subdir/test.txt');
        $content = 'test';
        $this->assertTrue(rex_file::put($file, $content), 'put() returns true on success');
        $this->assertEquals($content, rex_file::get($file), 'get() returns content of file');
    }

    public function testCopyToFile()
    {
        $orig = $this->getPath('orig.txt');
        $copy = $this->getPath('copy.txt');
        $content = 'test';
        rex_file::put($orig, $content);
        $this->assertTrue(rex_file::copy($orig, $copy), 'copy() returns true on success');
        $this->assertEquals($content, rex_file::get($orig), 'content of copied file has not changed');
        $this->assertEquals($content, rex_file::get($copy), 'content of new file is the same as of original file');
    }

    public function testCopyToDir()
    {
        $orig = $this->getPath('file.txt');
        $copyDir = $this->getPath('copy');
        $copyFile = $this->getPath('copy/file.txt');
        $content = 'test';
        rex_file::put($orig, $content);
        rex_dir::create($copyDir);
        $this->assertTrue(rex_file::copy($orig, $copyDir), 'copy() returns true on success');
        $this->assertEquals($content, rex_file::get($copyFile), 'content of new file is the same as of original file');
    }

    public function testDelete()
    {
        $file = $this->getPath('delete.txt');
        rex_file::put($file, '');
        $this->assertTrue(file_exists($file), 'file exists after put()');
        $this->assertTrue(rex_file::delete($file), 'delete() returns true on success');
        $this->assertFalse(file_exists($file), 'file does not exist after delete()');
        $this->assertTrue(rex_file::delete($file), 'delete() returns true when the file is already deleted');
    }

    public function testExtension()
    {
        $this->assertEquals('txt', rex_file::extension('test.txt'), 'extension() returns file extension');
        $this->assertEquals('txt', rex_file::extension('test.file.txt'), 'extension() returns file extension');
    }

    public function testGetOutput()
    {
        $file = $this->getPath('test.php');
        rex_file::put($file, 'a<?php echo "b";');
        $this->assertEquals('ab', rex_file::getOutput($file), 'getOutput() returns the executed content');
    }
}
