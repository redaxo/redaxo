<?php

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
class rex_file_test extends TestCase
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
        return rex_path::addonData('tests', 'rex_file_test/' . $file);
    }

    public function testRequireThrows(): void
    {
        $this->expectException(rex_exception::class);

        $file = $this->getPath('non_existing.txt');
        rex_file::require($file);
    }

    public function testGetDefault(): void
    {
        $file = $this->getPath('non_existing.txt');
        static::assertNull(rex_file::get($file), 'get() returns null for non-existing files');
        $myDefault = 'myDefault';
        static::assertEquals($myDefault, rex_file::get($file, $myDefault), 'get() returns given default value for non-existing files');
    }

    public function testGetConfigDefault(): void
    {
        $file = $this->getPath('non_existing.txt');
        static::assertEquals([], rex_file::getConfig($file), 'getConfig() returns empty array for non-existing files');
        $myDefault = ['myDefault'];
        static::assertEquals($myDefault, rex_file::getConfig($file, $myDefault), 'getConfig() returns given default value for non-existing files');
    }

    public function testGetCacheDefault(): void
    {
        $file = $this->getPath('non_existing.txt');
        static::assertEquals([], rex_file::getCache($file), 'getCache() returns empty array for non-existing files');
        $myDefault = ['myDefault'];
        static::assertEquals($myDefault, rex_file::getCache($file, $myDefault), 'getCache() returns given default value for non-existing files');
    }

    public function testPutGet(): void
    {
        $file = $this->getPath('putget.txt');
        $content = 'test';
        static::assertTrue(rex_file::put($file, $content), 'put() returns true on success');
        static::assertEquals($content, rex_file::get($file), 'get() returns content of file');
    }

    public function testPutGetConfig(): void
    {
        $file = $this->getPath('putgetcache.txt');
        $content = ['test', 'key' => 'value'];
        static::assertTrue(rex_file::putConfig($file, $content), 'putConfig() returns true on success');
        static::assertEquals($content, rex_file::getConfig($file), 'getConfig() returns content of file');
    }

    public function testPutGetCache(): void
    {
        $file = $this->getPath('putgetcache.txt');
        $content = ['test', 'key' => 'value'];
        static::assertTrue(rex_file::putCache($file, $content), 'putCache() returns true on success');
        static::assertEquals($content, rex_file::getCache($file), 'getCache() returns content of file');
    }

    public function testPutInNewDir(): void
    {
        $file = $this->getPath('subdir/test.txt');
        $content = 'test';
        static::assertTrue(rex_file::put($file, $content), 'put() returns true on success');
        static::assertEquals($content, rex_file::get($file), 'get() returns content of file');
    }

    public function testCopyToFile(): void
    {
        $orig = $this->getPath('orig.txt');
        $copy = $this->getPath('sub/copy.txt');
        $content = 'test';
        rex_file::put($orig, $content);
        static::assertTrue(rex_file::copy($orig, $copy), 'copy() returns true on success');
        static::assertEquals($content, rex_file::get($orig), 'content of copied file has not changed');
        static::assertEquals($content, rex_file::get($copy), 'content of new file is the same as of original file');
    }

    public function testCopyToDir(): void
    {
        $orig = $this->getPath('file.txt');
        $copyDir = $this->getPath('copy');
        $copyFile = $this->getPath('copy/file.txt');
        $content = 'test';
        rex_file::put($orig, $content);
        rex_dir::create($copyDir);
        static::assertTrue(rex_file::copy($orig, $copyDir), 'copy() returns true on success');
        static::assertEquals($content, rex_file::get($copyFile), 'content of new file is the same as of original file');
    }

    public function testDelete(): void
    {
        $file = $this->getPath('delete.txt');
        rex_file::put($file, '');
        static::assertFileExists($file, 'file exists after put()');
        static::assertTrue(rex_file::delete($file), 'delete() returns true on success');
        static::assertFileDoesNotExist($file, 'file does not exist after delete()');
        static::assertTrue(rex_file::delete($file), 'delete() returns true when the file is already deleted');
    }

    /** @return list<array{string, string}> */
    public static function dataTestExtension(): array
    {
        return [
            ['test.txt',      'txt'],
            ['test.file.txt', 'txt'],
            ['noextension',   ''],
            ['.hiddenfile',   'hiddenfile'],
        ];
    }

    #[DataProvider('dataTestExtension')]
    public function testExtension(string $file, string $expectedExtension): void
    {
        static::assertEquals($expectedExtension, rex_file::extension($file), 'extension() returns file extension');
    }

    /** @return list<array{string, string}> */
    public static function dataTestMimeType(): array
    {
        return [
            ['image/png', rex_path::pluginAssets('be_style', 'redaxo', 'icons/apple-touch-icon.png')],
            ['text/xml', rex_path::pluginAssets('be_style', 'redaxo', 'icons/browserconfig.xml')],
            ['text/css', rex_path::pluginAssets('be_style', 'redaxo', 'css/styles.css')],
            ['application/javascript', rex_path::pluginAssets('be_style', 'redaxo', 'javascripts/redaxo.js')],
            ['image/svg+xml', rex_path::addonAssets('be_style', 'images/redaxo-logo.svg')],
        ];
    }

    #[DataProvider('dataTestMimeType')]
    public function testMimeType(string $expectedMimeType, string $file): void
    {
        static::assertEquals($expectedMimeType, rex_file::mimeType($file));
    }

    public function testGetOutput(): void
    {
        $file = $this->getPath('test.php');
        rex_file::put($file, 'a<?php echo "b";');
        static::assertEquals('ab', rex_file::getOutput($file), 'getOutput() returns the executed content');
    }
}
