<?php

use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
class rex_article_test extends TestCase
{
    protected function setUp(): void
    {
        // generate classVars and add test column
        rex_article::getClassVars();
        $class = new ReflectionClass(rex_article::class);
        $classVarsProperty = $class->getProperty('classVars');
        $classVarsProperty->setValue(
            array_merge(
                $classVarsProperty->getValue(),
                ['art_foo'],
            ),
        );
    }

    protected function tearDown(): void
    {
        // reset static properties
        $class = new ReflectionClass(rex_article::class);
        $classVarsProperty = $class->getProperty('classVars');
        $classVarsProperty->setValue(null);

        rex_article::clearInstancePool();
    }

    public function testHasValue(): void
    {
        $instance = $this->createArticleWithoutConstructor();

        /** @psalm-suppress UndefinedPropertyAssignment */
        $instance->art_foo = 'teststring';

        static::assertTrue($instance->hasValue('foo'));
        static::assertTrue($instance->hasValue('art_foo'));

        static::assertFalse($instance->hasValue('bar'));
        static::assertFalse($instance->hasValue('art_bar'));
    }

    public function testGetValue(): void
    {
        $instance = $this->createArticleWithoutConstructor();

        /** @psalm-suppress UndefinedPropertyAssignment */
        $instance->art_foo = 'teststring';

        static::assertEquals('teststring', $instance->getValue('foo'));
        static::assertEquals('teststring', $instance->getValue('art_foo'));

        static::assertNull($instance->getValue('bar'));
        static::assertNull($instance->getValue('art_bar'));
    }

    private function createArticleWithoutConstructor(): rex_article
    {
        return (new ReflectionClass(rex_article::class))->newInstanceWithoutConstructor();
    }
}
