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
        /** @psalm-suppress MixedArgument */
        $class->setStaticPropertyValue('classVars', array_merge(
            $class->getStaticPropertyValue('classVars'),
            ['art_foo'],
        ));
    }

    protected function tearDown(): void
    {
        // reset static properties
        $class = new ReflectionClass(rex_article::class);
        $class->setStaticPropertyValue('classVars', null);

        rex_article::clearInstancePool();
    }

    public function testHasValue(): void
    {
        $instance = $this->createArticleWithoutConstructor();

        /** @psalm-suppress UndefinedPropertyAssignment */
        $instance->art_foo = 'teststring';

        self::assertTrue($instance->hasValue('foo'));
        self::assertTrue($instance->hasValue('art_foo'));

        self::assertFalse($instance->hasValue('bar'));
        self::assertFalse($instance->hasValue('art_bar'));
    }

    public function testGetValue(): void
    {
        $instance = $this->createArticleWithoutConstructor();

        /** @psalm-suppress UndefinedPropertyAssignment */
        $instance->art_foo = 'teststring';

        self::assertEquals('teststring', $instance->getValue('foo'));
        self::assertEquals('teststring', $instance->getValue('art_foo'));

        self::assertNull($instance->getValue('bar'));
        self::assertNull($instance->getValue('art_bar'));
    }

    private function createArticleWithoutConstructor(): rex_article
    {
        return (new ReflectionClass(rex_article::class))->newInstanceWithoutConstructor();
    }
}
