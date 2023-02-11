<?php

use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
class rex_dir_test extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        rex_dir::create($this->getPath());
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        rex_dir::delete($this->getPath());
    }

    private function getPath(string $file = ''): string
    {
        return rex_path::addonData('tests', 'rex_dir_test/' . $file);
    }

    public function testCreate(): void
    {
        $path = $this->getPath('create');
        static::assertTrue(rex_dir::create($path), 'create() returns true on success');
        static::assertDirectoryExists($path, 'dir exists after create()');
        static::assertTrue(rex_dir::create($path), 'create() on existing dirs returns also true');
    }

    public function testCreateRecursive(): void
    {
        $path = $this->getPath('create_recursive/test/test');
        static::assertTrue(rex_dir::create($path), 'create() returns true on success');
        static::assertDirectoryExists($path, 'dir exists after create()');
    }

    public function testCopyToNewDir(): void
    {
        $orig = $this->getPath('orig1');
        $copy = $this->getPath('copy1');
        rex_dir::create($orig . '/dir1');
        rex_file::put($orig . '/file.txt', '');
        rex_file::put($orig . '/dir2/file.txt', '');

        static::assertTrue(rex_dir::copy($orig, $copy), 'copy() returns true on success');
        static::assertDirectoryExists($copy . '/dir1', 'subdir exists after copy()');
        static::assertTrue(is_file($copy . '/file.txt'), 'file exists after copy()');
        static::assertTrue(is_file($copy . '/dir2/file.txt'), 'file in subdir exists after copy()');
    }

    public function testCopyToExistingDir(): void
    {
        $orig = $this->getPath('orig2');
        $copy = $this->getPath('copy2');
        // dir1 only in /orig
        rex_dir::create($orig . '/dir1');
        // dir2 only in /copy
        rex_dir::create($copy . '/dir2');
        // file1 only in /orig
        rex_file::put($orig . '/file1.txt', '');
        rex_file::put($orig . '/dir3/file1.txt', '');
        // file2 with newest version in /orig
        rex_file::put($copy . '/file2.txt', 'file2_old');
        touch($copy . '/file2.txt', 1);
        rex_file::put($copy . '/dir3/file2.txt', 'file2_old');
        touch($copy . '/dir3/file2.txt', 1);
        rex_file::put($orig . '/file2.txt', 'file2_new');
        rex_file::put($orig . '/dir3/file2.txt', 'file2_new');

        static::assertTrue(rex_dir::copy($orig, $copy), 'copy() returns true on success');
        static::assertDirectoryExists($copy . '/dir1', 'subdir of source dir exists in destination dir');
        static::assertDirectoryExists($copy . '/dir2', 'existsing subdir of destination dir still exists');
        static::assertTrue(is_file($copy . '/file1.txt'), 'file of source dir exists in destination dir');
        static::assertTrue(is_file($copy . '/dir3/file1.txt'), 'existing file of destination dir still exists');
        static::assertEquals('file2_new', rex_file::get($copy . '/file2.txt'), 'existing file in destination dir will be replaced');
        static::assertEquals('file2_new', rex_file::get($copy . '/dir3/file2.txt'), 'existing file in destination dir will be replaced');
    }

    public function testDeleteComplete(): void
    {
        $dir = $this->getPath('deleteComplete');
        $file = $this->getPath('deleteComplete/subdir/file.txt');
        rex_file::put($file, '');

        static::assertTrue(is_file($file), 'file exists after put()');
        static::assertTrue(rex_dir::delete($dir), 'delete() returns true on success');
        static::assertDirectoryDoesNotExist($dir, 'dir does not exist after complete delete()');
    }

    public function testDeleteWithoutSelf(): void
    {
        $dir = $this->getPath('deleteCompleteWithoutSelf');
        $file = $this->getPath('deleteCompleteWithoutSelf/subdir/file.txt');
        rex_file::put($file, '');

        static::assertTrue(is_file($file), 'file exists after put()');
        static::assertTrue(rex_dir::delete($dir, false), 'delete() returns true on success');
        static::assertFalse(is_file($file), 'file does not exist after delete()');
        static::assertDirectoryDoesNotExist($dir . '/subdir', 'subdir does not exist after delete()');
        static::assertDirectoryExists($dir, 'main dir still exists after delete() without self');
    }

    public function testDeleteFilesNotRecursive(): void
    {
        $dir = $this->getPath('deleteFilesNotRecursive');
        $file1 = $this->getPath('deleteFilesNotRecursive/file.txt');
        $file2 = $this->getPath('deleteFilesNotRecursive/subdir/file.txt');
        rex_file::put($file1, '');
        rex_file::put($file2, '');

        static::assertTrue(is_file($file1), 'file exists after put()');
        static::assertTrue(is_file($file2), 'file exists after put()');
        static::assertTrue(rex_dir::deleteFiles($dir, false), 'deleteFiles() returns true on success');
        static::assertFalse(is_file($file1), 'file in main dir does not exist after deleteFiles()');
        static::assertTrue(is_file($file2), 'file in subdir still exists after non-recursive deleteFiles()');
    }

    public function testDeleteFilesRecursive(): void
    {
        $dir = $this->getPath('deleteFilesRecursive');
        $file1 = $this->getPath('deleteFilesRecursive/file.txt');
        $file2 = $this->getPath('deleteFilesRecursive/subdir/file.txt');
        rex_file::put($file1, '');
        rex_file::put($file2, '');

        static::assertTrue(is_file($file1), 'file exists after put()');
        static::assertTrue(is_file($file2), 'file exists after put()');
        static::assertTrue(rex_dir::deleteFiles($dir), 'deleteFiles() returns true on success');
        static::assertFalse(is_file($file1), 'file in main dir does not exist after deleteFiles()');
        static::assertFalse(is_file($file2), 'file in subdir does not exist after recursive deleteFiles()');
        static::assertDirectoryExists($dir . '/subdir', 'subdir still exists after deleteFiles()');
    }
}
