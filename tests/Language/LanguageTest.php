<?php

namespace Redaxo\Core\Tests\Language;

use PHPUnit\Framework\TestCase;
use Redaxo\Core\Language\Language;
use ReflectionClass;

/** @internal */
final class LanguageTest extends TestCase
{
    public function testGetCurrentId(): void
    {
        self::assertIsInt(Language::getCurrentId());
    }

    public function testGetId(): void
    {
        self::assertIsInt(Language::getCurrent()->getId());
    }

    public function testGetPriority(): void
    {
        self::assertSame(1, Language::getCurrent()->getPriority());
    }

    public function testIsOnline(): void
    {
        self::assertIsBool(Language::getCurrent()->isOnline());
    }

    public function testHasValue(): void
    {
        $clang = $this->createClangWithoutConstructor();

        /** @psalm-suppress UndefinedPropertyAssignment */
        $clang->clang_foo = 'teststring'; // @phpstan-ignore-line

        self::assertTrue($clang->hasValue('foo'));
        self::assertTrue($clang->hasValue('clang_foo'));

        self::assertFalse($clang->hasValue('bar'));
        self::assertFalse($clang->hasValue('clang_bar'));
    }

    public function testGetValue(): void
    {
        self::assertIsInt(Language::getCurrent()->getValue('id'));

        $clang = $this->createClangWithoutConstructor();

        /** @psalm-suppress UndefinedPropertyAssignment */
        $clang->clang_foo = 'teststring'; // @phpstan-ignore-line

        self::assertEquals('teststring', $clang->getValue('foo'));
        self::assertEquals('teststring', $clang->getValue('clang_foo'));

        self::assertNull($clang->getValue('bar'));
        self::assertNull($clang->getValue('clang_bar'));
    }

    private function createClangWithoutConstructor(): Language
    {
        return (new ReflectionClass(Language::class))->newInstanceWithoutConstructor();
    }
}
