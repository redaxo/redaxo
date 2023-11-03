<?php

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
class rex_article_content_base_test extends TestCase
{
    public function testHasValue(): void
    {
        $instance = $this->createArticleContentBaseWithoutConstructor();

        // fake meta field in database structure
        $propArticle = new ReflectionProperty(rex_article_content_base::class, 'ARTICLE');
        $propArticle->setValue($instance, rex_sql::factory()->setValue('art_foo', 'teststring'));

        static::assertTrue($instance->hasValue('foo'));
        static::assertTrue($instance->hasValue('art_foo'));

        static::assertFalse($instance->hasValue('bar'));
        static::assertFalse($instance->hasValue('art_bar'));
    }

    public function testGetValue(): void
    {
        $instance = $this->createArticleContentBaseWithoutConstructor();

        // fake meta field in database structure
        $propArticle = new ReflectionProperty(rex_article_content_base::class, 'ARTICLE');
        $propArticle->setValue($instance, rex_sql::factory()->setValue('art_foo', 'teststring'));

        static::assertEquals('teststring', $instance->getValue('foo'));
        static::assertEquals('teststring', $instance->getValue('art_foo'));
    }

    #[DataProvider('dataGetValueNonExisting')]
    public function testGetValueNonExisting(string $value): void
    {
        $instance = $this->createArticleContentBaseWithoutConstructor();

        $this->expectException(rex_exception::class);

        $instance->getValue($value);
    }

    /** @return list<array{string}> */
    public static function dataGetValueNonExisting(): array
    {
        return [
            ['bar'],
            ['art_bar'],
        ];
    }

    private function createArticleContentBaseWithoutConstructor(): rex_article_content_base
    {
        return (new ReflectionClass(rex_article_content_base::class))->newInstanceWithoutConstructor();
    }
}
