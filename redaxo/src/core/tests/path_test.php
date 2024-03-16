<?php

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/** @internal */
final class rex_path_test extends TestCase
{
    public function testAbsoluteConversion(): void
    {
        $path = rex_path::absolute('c:/abc/../def/./xy');
        self::assertEquals($this->path('c:/def/xy'), $path, 'resolves .. and .');

        $path = rex_path::absolute('c:\abc\..\def\.\xy');
        self::assertEquals($this->path('c:\def\xy'), $path, 'resolves .. and .');
    }

    #[DataProvider('dataRelative')]
    public function testRelative(string $expected, string $path, ?string $basePath = null): void
    {
        self::assertSame($this->path($expected), rex_path::relative($path, $basePath));
    }

    /** @return list<array{0: string, 1: string, 2?: string}> */
    public static function dataRelative(): array
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

    public function testBasename(): void
    {
        self::assertSame('config.yml', rex_path::basename('../redaxo/data/core/config.yml'));

        self::assertSame('config.yml', rex_path::basename('..\redaxo\data\core\config.yml'));
    }

    public function testFindBinaryPath(): void
    {
        $path = rex_path::findBinaryPath('php');
        self::assertNotNull($path);
        self::assertSame(PHP_BINARY, realpath($path));
    }

    public function testNotFoundBinaryPath(): void
    {
        self::assertNull(rex_path::findBinaryPath('noone-knows'));
    }

    private function path(string $path): string
    {
        return str_replace(['\\', '/'], DIRECTORY_SEPARATOR, $path);
    }
}
