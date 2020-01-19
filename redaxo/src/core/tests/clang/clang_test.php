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

        $this->assertTrue($clang->hasValue('foo'));
        $this->assertTrue($clang->hasValue('clang_foo'));

        $this->assertFalse($clang->hasValue('bar'));
        $this->assertFalse($clang->hasValue('clang_bar'));
    }

    public function testGetValue()
    {
        $clangClass = new ReflectionClass(rex_clang::class);
        /** @var rex_clang $clang */
        $clang = $clangClass->newInstanceWithoutConstructor();

        $clang->clang_foo = 'teststring';

        $this->assertEquals('teststring', $clang->getValue('foo'));
        $this->assertEquals('teststring', $clang->getValue('clang_foo'));

        $this->assertNull($clang->getValue('bar'));
        $this->assertNull($clang->getValue('clang_bar'));
    }
}
