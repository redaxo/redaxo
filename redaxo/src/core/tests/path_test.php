<?php

use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
class rex_path_test extends TestCase
{
    public function testAbsoluteConversion()
    {
        $path = rex_path::absolute('c:/abc/../def/./xy');
        static::assertEquals($this->path('c:/def/xy'), $path, 'resolves .. and .');

        $path = rex_path::absolute('c:\abc\..\def\.\xy');
        static::assertEquals($this->path('c:\def\xy'), $path, 'resolves .. and .');
    }

    /**
     * @dataProvider dataRelative
     */
    public function testRelative($expected, $path, $basePath = null)
    {
        static::assertSame($this->path($expected), rex_path::relative($path, $basePath));
    }

    public function dataRelative()
    {
        return [
            ['redaxo/src/core/boot.php', rex_path::core('boot.php')],
            ['/foo/bar/baz', '/foo/bar/baz'],
            ['baz/qux', '/foo/bar/baz/qux', '/foo/bar'],
            ['baz/qux/', '/foo/bar/baz/qux/', '/foo/bar'],
            ['baz/qux', '/foo/bar/baz/qux', '/foo/bar/'],
            ['/foo/barbaz/qux', '/foo/barbaz/qux', '/foo/bar'],
            ['baz/qux', '/foo/bar/baz/qux', '\foo\bar'],
            ['/foo/bar/baz/qux', '/foo/bar/baz/qux', '/abc/foo/bar'],
        ];
    }

    public function testBasename()
    {
        static::assertSame('config.yml', rex_path::basename('../redaxo/data/core/config.yml'));

        static::assertSame('config.yml', rex_path::basename('..\redaxo\data\core\config.yml'));
    }

    private function path($path)
    {
        return str_replace(['\\', '/'], DIRECTORY_SEPARATOR, $path);
    }
}
