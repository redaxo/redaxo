<?php

use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
class rex_version_test extends TestCase
{
    public function testIsUnstable()
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

    public function splitProvider()
    {
        return [
            ['1.1.2',      ['1', '1', '2']],
            ['1.2alpha1',  ['1', '2', 'alpha', '1']],
            ['1_2 beta 2', ['1', '2', 'beta', '2']],
            ['2.2.3-dev',  ['2', '2', '3', 'dev']],
        ];
    }

    /**
     * @dataProvider splitProvider
     */
    public function testSplit($version, $expected)
    {
        static::assertEquals($expected, rex_version::split($version));
    }

    public function compareProvider()
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

    /**
     * @dataProvider compareProvider
     *
     * @param null|'='|'=='|'!='|'<>'|'<'|'<='|'>'|'>=' $comparator
     */
    public function testCompare($expected, string $version1, string $version2, ?string $comparator)
    {
        static::assertSame($expected, rex_version::compare($version1, $version2, $comparator));
    }

    public function testGitHash(): void
    {
        static::assertIsString(rex_version::gitHash(__DIR__));

        static::assertNull(rex_version::gitHash(__DIR__, 'foo/bar'));
    }
}
