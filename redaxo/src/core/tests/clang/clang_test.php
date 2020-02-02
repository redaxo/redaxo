<?php

use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
class rex_clang_test extends TestCase
{
    public function testHasValue()
    {
        $clangClass = new ReflectionClass(rex_clang::class);
        /** @var rex_clang $clang */
        $clang = $clangClass->newInstanceWithoutConstructor();

        $clang->clang_foo = 'teststring';

        static::assertTrue($clang->hasValue('foo'));
        static::assertTrue($clang->hasValue('clang_foo'));

        static::assertFalse($clang->hasValue('bar'));
        static::assertFalse($clang->hasValue('clang_bar'));
    }

    public function testGetValue()
    {
        $clangClass = new ReflectionClass(rex_clang::class);
        /** @var rex_clang $clang */
        $clang = $clangClass->newInstanceWithoutConstructor();

        $clang->clang_foo = 'teststring';

        static::assertEquals('teststring', $clang->getValue('foo'));
        static::assertEquals('teststring', $clang->getValue('clang_foo'));

        static::assertNull($clang->getValue('bar'));
        static::assertNull($clang->getValue('clang_bar'));
    }
}
