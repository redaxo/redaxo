<?php

use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
class rex_article_content_test extends TestCase
{
    protected function setUp()
    {
        // fake article
        $article_file = rex_path::addonCache('structure', '1.1.article');
        rex_file::putCache($article_file, [
            'pid' => 1,
            'id' => 1,
            'parent_id' => 0,
            'name' => 'Testarticle',
            'catname' => 'Testcategory',
            'catpriority' => 1,
            'startarticle' => 1,
            'priority' => 1,
            'path' => '|',
            'status' => 1,
            'template_id' => 1,
            'clang_id' => 1,
            'createdate' => '2020-01-01 12:30:00',
            'createuser' => 'tests',
            'updatedate' => '2020-01-02 13:40:00',
            'updateuser' => 'tests',
            'revision' => 0,

            'art_foo' => 'teststring',
        ]);

        // generate classVars and add test column
        rex_article::getClassVars();
        $class = new ReflectionClass(rex_article::class);
        $classVarsProperty = $class->getProperty('classVars');
        $classVarsProperty->setAccessible(true);
        $classVarsProperty->setValue(
            array_merge(
                $classVarsProperty->getValue(),
                ['art_foo']
            )
        );
    }

    protected function tearDown()
    {
        // delete all fake structure cache files
        $finder = rex_finder::factory(rex_path::addonCache('structure'))
            ->recursive()
            ->childFirst()
            ->ignoreSystemStuff(false);
        rex_dir::deleteIterator($finder);

        // reset static properties
        $class = new ReflectionClass(rex_article::class);
        $classVarsProperty = $class->getProperty('classVars');
        $classVarsProperty->setAccessible(true);
        $classVarsProperty->setValue(null);

        rex_article::clearInstancePool();
    }

    public function testBcHasValue()
    {
        $class = new ReflectionClass(rex_article_content::class);

        /** @var rex_article_content $instance */
        $instance = $class->newInstance(1, 1);

        $viaSql = new ReflectionProperty(rex_article_content::class, 'viasql');
        $viaSql->setAccessible(true);
        $viaSql->setValue($instance, true);

        // fake meta field in database structure
        $propArticle = new ReflectionProperty(rex_article_content_base::class, 'ARTICLE');
        $propArticle->setAccessible(true);
        $propArticle->setValue($instance, rex_sql::factory()->setValue('art_foo', 'teststring'));

        static::assertTrue($instance->hasValue('foo'));
        static::assertTrue($instance->hasValue('art_foo'));

        static::assertFalse($instance->hasValue('bar'));
        static::assertFalse($instance->hasValue('art_bar'));
    }

    public function testBcGetValue()
    {
        $class = new ReflectionClass(rex_article_content::class);
        /** @var rex_article_content $instance */
        $instance = $class->newInstance(1, 1);

        $viaSql = new ReflectionProperty(rex_article_content::class, 'viasql');
        $viaSql->setAccessible(true);
        $viaSql->setValue($instance, true);

        // fake meta field in database structure
        $propArticle = new ReflectionProperty(rex_article_content_base::class, 'ARTICLE');
        $propArticle->setAccessible(true);
        $propArticle->setValue($instance, rex_sql::factory()->setValue('art_foo', 'teststring'));

        static::assertEquals('teststring', $instance->getValue('foo'));
        static::assertEquals('teststring', $instance->getValue('art_foo'));

        static::assertEquals('[bar not found]', $instance->getValue('bar'));
        static::assertEquals('[art_bar not found]', $instance->getValue('art_bar'));
    }

    public function testHasValue()
    {
        $class = new ReflectionClass(rex_article_content::class);

        /** @var rex_article_content $instance */
        $instance = $class->newInstance(1, 1);

        static::assertTrue($instance->hasValue('foo'));
        static::assertTrue($instance->hasValue('art_foo'));

        static::assertFalse($instance->hasValue('bar'));
        static::assertFalse($instance->hasValue('art_bar'));
    }

    public function testGetValue()
    {
        $class = new ReflectionClass(rex_article_content::class);
        /** @var rex_article_content $instance */
        $instance = $class->newInstance(1, 1);

        static::assertEquals('teststring', $instance->getValue('foo'));
        static::assertEquals('teststring', $instance->getValue('art_foo'));

        static::assertEquals('[bar not found]', $instance->getValue('bar'));
        static::assertEquals('[art_bar not found]', $instance->getValue('art_bar'));
    }
}
