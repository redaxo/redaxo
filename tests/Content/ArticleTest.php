<?php

namespace Redaxo\Core\Tests\Content;

use PHPUnit\Framework\TestCase;
use Redaxo\Core\Content\Article;
use ReflectionClass;

/** @internal */
final class ArticleTest extends TestCase
{
    protected function setUp(): void
    {
        // generate classVars and add test column
        Article::getClassVars();
        $class = new ReflectionClass(Article::class);
        /** @psalm-suppress MixedArgument */
        $class->setStaticPropertyValue('classVars', array_merge(
            $class->getStaticPropertyValue('classVars'),
            ['art_foo'],
        ));
    }

    protected function tearDown(): void
    {
        // reset static properties
        $class = new ReflectionClass(Article::class);
        $class->setStaticPropertyValue('classVars', null);

        Article::clearInstancePool();
    }

    public function testHasValue(): void
    {
        $instance = $this->createArticleWithoutConstructor();

        /** @psalm-suppress UndefinedPropertyAssignment */
        $instance->art_foo = 'teststring'; // @phpstan-ignore-line

        self::assertTrue($instance->hasValue('foo'));
        self::assertTrue($instance->hasValue('art_foo'));

        self::assertFalse($instance->hasValue('bar'));
        self::assertFalse($instance->hasValue('art_bar'));
    }

    public function testGetValue(): void
    {
        $instance = $this->createArticleWithoutConstructor();

        /** @psalm-suppress UndefinedPropertyAssignment */
        $instance->art_foo = 'teststring'; // @phpstan-ignore-line

        self::assertEquals('teststring', $instance->getValue('foo'));
        self::assertEquals('teststring', $instance->getValue('art_foo'));

        self::assertNull($instance->getValue('bar'));
        self::assertNull($instance->getValue('art_bar'));
    }

    private function createArticleWithoutConstructor(): Article
    {
        return (new ReflectionClass(Article::class))->newInstanceWithoutConstructor();
    }
}
