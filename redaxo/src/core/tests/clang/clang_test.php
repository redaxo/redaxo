<?php

use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
class rex_clang_test extends TestCase
{
    public function testGetCurrentId(): void
    {
        static::assertIsInt(rex_clang::getCurrentId());
    }

    public function testGetId(): void
    {
        static::assertIsInt(rex_clang::getCurrent()->getId());
    }

    public function testGetPriority(): void
    {
        static::assertSame(1, rex_clang::getCurrent()->getPriority());
    }

    public function testIsOnline(): void
    {
        static::assertIsBool(rex_clang::getCurrent()->isOnline());
    }

    public function testHasValue()
    {
        $clangClass = new ReflectionClass(rex_clang::class);
        /** @var rex_clang $clang */
        $clang = $clangClass->newInstanceWithoutConstructor();

        /** @psalm-suppress UndefinedPropertyAssignment */
        $clang->clang_foo = 'teststring';

        static::assertTrue($clang->hasValue('foo'));
        static::assertTrue($clang->hasValue('clang_foo'));

        static::assertFalse($clang->hasValue('bar'));
        static::assertFalse($clang->hasValue('clang_bar'));
    }

    public function testGetValue()
    {
        static::assertIsInt(rex_clang::getCurrent()->getValue('id'));

        $clangClass = new ReflectionClass(rex_clang::class);
        /** @var rex_clang $clang */
        $clang = $clangClass->newInstanceWithoutConstructor();

        /** @psalm-suppress UndefinedPropertyAssignment */
        $clang->clang_foo = 'teststring';

        static::assertEquals('teststring', $clang->getValue('foo'));
        static::assertEquals('teststring', $clang->getValue('clang_foo'));

        static::assertNull($clang->getValue('bar'));
        static::assertNull($clang->getValue('clang_bar'));
    }
}
