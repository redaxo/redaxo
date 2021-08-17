<?php

use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
class rex_article_content_base_test extends TestCase
{
    public function testHasValue()
    {
        $instance = $this->createArticleContentBaseWithoutConstructor();

        // fake meta field in database structure
        $propArticle = new ReflectionProperty(rex_article_content_base::class, 'ARTICLE');
        $propArticle->setAccessible(true);
        $propArticle->setValue($instance, rex_sql::factory()->setValue('art_foo', 'teststring'));

        static::assertTrue($instance->hasValue('foo'));
        static::assertTrue($instance->hasValue('art_foo'));

        static::assertFalse($instance->hasValue('bar'));
        static::assertFalse($instance->hasValue('art_bar'));
    }

    public function testGetValue()
    {
        $instance = $this->createArticleContentBaseWithoutConstructor();

        // fake meta field in database structure
        $propArticle = new ReflectionProperty(rex_article_content_base::class, 'ARTICLE');
        $propArticle->setAccessible(true);
        $propArticle->setValue($instance, rex_sql::factory()->setValue('art_foo', 'teststring'));

        static::assertEquals('teststring', $instance->getValue('foo'));
        static::assertEquals('teststring', $instance->getValue('art_foo'));

        static::assertEquals('[bar not found]', $instance->getValue('bar'));
        static::assertEquals('[art_bar not found]', $instance->getValue('art_bar'));
    }

    private function createArticleContentBaseWithoutConstructor(): rex_article_content_base
    {
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return (new ReflectionClass(rex_article_content_base::class))->newInstanceWithoutConstructor();
    }
}
