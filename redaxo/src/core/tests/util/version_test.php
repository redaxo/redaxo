<?php

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
class rex_version_test extends TestCase
{
    public function testIsUnstable(): void
    {
        static::assertTrue(rex_version::isUnstable('1.0-dev'));
        static::assertTrue(rex_version::isUnstable('2.1.0beta'));
        static::assertTrue(rex_version::isUnstable('1.0b1'));
        static::assertTrue(rex_version::isUnstable('3.5.1-alpha'));
        static::assertTrue(rex_version::isUnstable('99.99RC5'));
        static::assertTrue(rex_version::isUnstable('199.199 RC3'));

        static::assertFalse(rex_version::isUnstable('1.0'));
        static::assertFalse(rex_version::isUnstable('1.0-final'));
        static::assertFalse(rex_version::isUnstable('2.45 stable'));
        static::assertFalse(rex_version::isUnstable('1.0 codename starship'));
    }

    /** @return list<array{string, list<string>}> */
    public static function splitProvider(): array
    {
        return [
            ['1.1.2',      ['1', '1', '2']],
            ['1.2alpha1',  ['1', '2', 'alpha', '1']],
            ['1_2 beta 2', ['1', '2', 'beta', '2']],
            ['2.2.3-dev',  ['2', '2', '3', 'dev']],
        ];
    }

    /** @param list<string> $expected */
    #[DataProvider('splitProvider')]
    public function testSplit(string $version, array $expected): void
    {
        static::assertEquals($expected, rex_version::split($version));
    }

    /** @return list<array{bool, string, string, null|'='|'=='|'!='|'<>'|'<'|'<='|'>'|'>='}> */
    public static function compareProvider(): array
    {
        return [
            [true, '1',      '1',      '='],
            [true, '1.0',    '1.0',    '='],
            [true, '1',      '1.0',    '='],
            [true, '1.0 a1', '1.0.a1', '='],
            [true, '1.0a1',  '1.0.a1', '='],
            [true, '1.0 alpha 1', '1.0.a1', '='],

            [true, '1',      '2',        '<'],
            [true, '1',      '1.1',      '<'],
            [true, '1.0',    '1.1',      '<'],
            [true, '1.1',    '1.2',      '<'],
            [true, '1.2',    '1.10',     '<'],
            [true, '1.a1',   '1',        '<'],
            [true, '1.a1',   '1.0',      '<'],
            [true, '1.a1',   '1.a2',     '<'],
            [true, '1.a1',   '1.b1',     '<'],
            [true, '1.0.a1', '1',        '<'],
            [true, '1.0.a1', '1.0.0.0.', '<'],
            [true, '1.0a1',  '1.0',      '<'],
            [true, '1.0a1',  '1.0.1',    '<'],
            [true, '1.0a1',  '1.0a2',    '<'],
            [true, '1.0',    '1.1a1',    '<'],
            [true, '1.0.1',  '1.1a1',    '<'],

            [false, '1.0', '1.0', null],
            [true, '1.0', '1.1', null],
            [false, '1.1', '1.0', null],
        ];
    }

    /** @param null|'='|'=='|'!='|'<>'|'<'|'<='|'>'|'>=' $comparator */
    #[DataProvider('compareProvider')]
    public function testCompare(bool $expected, string $version1, string $version2, ?string $comparator): void
    {
        static::assertSame($expected, rex_version::compare($version1, $version2, $comparator));
    }

    public function testGitHash(): void
    {
        static::assertIsString(rex_version::gitHash(__DIR__));

        static::assertNull(rex_version::gitHash(__DIR__, 'foo/bar'));
    }

    #[DataProvider('dataMatchVersionConstraints')]
    public function testMatchVersionConstraints(bool $expected, string $version, string $constraints): void
    {
        static::assertSame($expected, rex_version::matchesConstraints($version, $constraints));
    }

    /** @return list<array{bool, string, string}> */
    public static function dataMatchVersionConstraints(): array
    {
        return [
            [true, '1.0.4', '1.0.4'],
            [false, '1.0.4', '1.0.5'],
            [true, '1.0.4', '*'],
            [true, '2.5.3', '2.*'],
            [false, '1.1', '2.*'],
            [false, '13.0', '12.*'],
            [false, '1.1', '1.2.*'],
            [false, '1.3', '1.2.*'],
            [true, '1.2.1', '1.2.*'],
            [false, '1.0.4', '>=1.1'],
            [false, '1.1.0-beta1', '>=1.1'],
            [true, '1.1.0', '>=1.1'],
            [true, '2.0', '>=1.1'],
            [false, '3.0', '>=1.1, <3.0'],
            [false, '1.0', '^1.0.3'],
            [true, '1.0.3', '^1.0.3'],
            [true, '1.9', '^1.0.3'],
            [false, '2.0', '^1.0.3'],
            [false, '2.0-beta1', '^1.0.3'],
            [true, '1.0.3', '~1.0.3'],
            [true, '1.0.5', '~1.0.3'],
            [false, '1.1', '~1.0.3'],
            [false, '1.1-beta1', '~1.0.3'],
        ];
    }
}
