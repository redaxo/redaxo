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
}
