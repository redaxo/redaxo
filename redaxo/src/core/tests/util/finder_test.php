<?php

use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
class rex_finder_test extends TestCase
{
    protected function setUp()
    {
        parent::setUp();

        rex_file::put($this->getPath('file1.txt'), '');
        rex_file::put($this->getPath('file2.yml'), '');
        rex_file::put($this->getPath('dir1/file3.txt'), '');
        rex_file::put($this->getPath('dir2/file4.yml'), '');
        rex_file::put($this->getPath('dir2/dir/file5.yml'), '');
        rex_dir::create($this->getPath('dir1/dir'));
        rex_dir::create($this->getPath('dir2/dir1'));
        rex_dir::create($this->getPath('dir'));
        rex_file::put($this->getPath('.DS_Store'), '');
        rex_file::put($this->getPath('dir1/Thumbs.db'), '');
    }

    protected function tearDown()
    {
        parent::tearDown();

        rex_dir::delete($this->getPath());
    }

    public function getPath($file = '')
    {
        return rex_path::addonData('tests', 'rex_finder_test/' . $file);
    }

    private function assertIteratorContains($iterator, $contains)
    {
        $array = iterator_to_array($iterator, true);
        static::assertCount(count($contains), $array);
        foreach ($contains as $file) {
            static::assertArrayHasKey($this->getPath($file), $array, $file . ' is in array');
        }
    }

    public function testDefault()
    {
        $iterator = rex_finder::factory($this->getPath());
        $this->assertIteratorContains($iterator, ['file1.txt', 'file2.yml', 'dir1', 'dir2', 'dir']);
    }

    public function testRecursive()
    {
        $iterator = rex_finder::factory($this->getPath())->recursive();
        $this->assertIteratorContains($iterator, ['file1.txt', 'file2.yml', 'dir1', 'dir1/file3.txt', 'dir1/dir', 'dir2', 'dir2/file4.yml', 'dir2/dir', 'dir2/dir/file5.yml', 'dir2/dir1', 'dir']);
    }

    public function testFilesOnly()
    {
        $iterator = rex_finder::factory($this->getPath())->recursive()->filesOnly();
        $this->assertIteratorContains($iterator, ['file1.txt', 'file2.yml', 'dir1/file3.txt', 'dir2/file4.yml', 'dir2/dir/file5.yml']);
    }

    public function testDirsOnly()
    {
        $iterator = rex_finder::factory($this->getPath())->recursive()->dirsOnly();
        $this->assertIteratorContains($iterator, ['dir1', 'dir1/dir', 'dir2', 'dir2/dir', 'dir2/dir1', 'dir']);
    }

    public function testIgnoreFiles()
    {
        $iterator = rex_finder::factory($this->getPath())
            ->recursive()
            ->ignoreFiles('*.txt', false)
            ->ignoreFiles(['file2.yml', 'file4*']);
        $this->assertIteratorContains($iterator, ['dir1', 'dir1/file3.txt', 'dir1/dir', 'dir2', 'dir2/dir', 'dir2/dir/file5.yml', 'dir2/dir1', 'dir']);
    }

    public function testIgnoreDirs()
    {
        $iterator = rex_finder::factory($this->getPath())
            ->recursive()
            ->ignoreDirs('dir', false)
            ->ignoreDirs('dir1');
        $this->assertIteratorContains($iterator, ['file1.txt', 'file2.yml', 'dir2', 'dir2/file4.yml', 'dir2/dir', 'dir2/dir/file5.yml']);
    }

    public function testIgnoreSystemStuff()
    {
        $iterator = rex_finder::factory($this->getPath())->recursive()->ignoreSystemStuff(false);
        $this->assertIteratorContains($iterator, ['file1.txt', 'file2.yml', 'dir1', 'dir1/file3.txt', 'dir1/dir', 'dir2', 'dir2/file4.yml', 'dir2/dir', 'dir2/dir/file5.yml', 'dir2/dir1', 'dir', '.DS_Store', 'dir1/Thumbs.db']);
    }
}
