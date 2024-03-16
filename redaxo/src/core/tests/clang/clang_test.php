<?php

use PHPUnit\Framework\TestCase;

/** @internal */
final class rex_clang_test extends TestCase
{
    public function testGetCurrentId(): void
    {
        self::assertIsInt(rex_clang::getCurrentId());
    }

    public function testGetId(): void
    {
        self::assertIsInt(rex_clang::getCurrent()->getId());
    }

    public function testGetPriority(): void
    {
        self::assertSame(1, rex_clang::getCurrent()->getPriority());
    }

    public function testIsOnline(): void
    {
        self::assertIsBool(rex_clang::getCurrent()->isOnline());
    }

    public function testHasValue(): void
    {
        $clang = $this->createClangWithoutConstructor();

        /** @psalm-suppress UndefinedPropertyAssignment */
        $clang->clang_foo = 'teststring';

        self::assertTrue($clang->hasValue('foo'));
        self::assertTrue($clang->hasValue('clang_foo'));

        self::assertFalse($clang->hasValue('bar'));
        self::assertFalse($clang->hasValue('clang_bar'));
    }

    public function testGetValue(): void
    {
        self::assertIsInt(rex_clang::getCurrent()->getValue('id'));

        $clang = $this->createClangWithoutConstructor();

        /** @psalm-suppress UndefinedPropertyAssignment */
        $clang->clang_foo = 'teststring';

        self::assertEquals('teststring', $clang->getValue('foo'));
        self::assertEquals('teststring', $clang->getValue('clang_foo'));

        self::assertNull($clang->getValue('bar'));
        self::assertNull($clang->getValue('clang_bar'));
    }

    private function createClangWithoutConstructor(): rex_clang
    {
        return (new ReflectionClass(rex_clang::class))->newInstanceWithoutConstructor();
    }
}
