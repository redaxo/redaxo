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
            ['1',      '1',      '='],
            ['1.0',    '1.0',    '='],
            ['1',      '1.0',    '='],
            ['1.0 a1', '1.0.a1', '='],
            ['1.0a1',  '1.0.a1', '='],
            ['1.0 alpha 1', '1.0.a1', '='],

            ['1',      '2',        '<'],
            ['1',      '1.1',      '<'],
            ['1.0',    '1.1',      '<'],
            ['1.1',    '1.2',      '<'],
            ['1.2',    '1.10',     '<'],
            ['1.a1',   '1',        '<'],
            ['1.a1',   '1.0',      '<'],
            ['1.a1',   '1.a2',     '<'],
            ['1.a1',   '1.b1',     '<'],
            ['1.0.a1', '1',        '<'],
            ['1.0.a1', '1.0.0.0.', '<'],
            ['1.0a1',  '1.0',      '<'],
            ['1.0a1',  '1.0.1',    '<'],
            ['1.0a1',  '1.0a2',    '<'],
            ['1.0',    '1.1a1',    '<'],
            ['1.0.1',  '1.1a1',    '<'],
        ];
    }

    /**
     * @dataProvider compareProvider
     */
    public function testCompare($version1, $version2, $comparator)
    {
        static::assertTrue(rex_version::compare($version1, $version2, $comparator));
    }
}
