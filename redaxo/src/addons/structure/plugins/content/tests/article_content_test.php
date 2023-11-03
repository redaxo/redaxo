<?php

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
class rex_article_content_test extends TestCase
{
    protected function setUp(): void
    {
        // fake article
        $articleFile = rex_path::addonCache('structure', '1.1.article');
        rex_file::putCache($articleFile, [
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
        $classVarsProperty->setValue(
            array_merge(
                $classVarsProperty->getValue(),
                ['art_foo'],
            ),
        );
    }

    protected function tearDown(): void
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
        $classVarsProperty->setValue(null);

        rex_article::clearInstancePool();
    }

    public function testBcHasValue(): void
    {
        $instance = new rex_article_content(1, 1);

        $viaSql = new ReflectionProperty(rex_article_content::class, 'viasql');
        $viaSql->setValue($instance, true);

        // fake meta field in database structure
        $propArticle = new ReflectionProperty(rex_article_content_base::class, 'ARTICLE');
        $propArticle->setValue($instance, rex_sql::factory()->setValue('art_foo', 'teststring'));

        static::assertTrue($instance->hasValue('foo'));
        static::assertTrue($instance->hasValue('art_foo'));

        static::assertFalse($instance->hasValue('bar'));
        static::assertFalse($instance->hasValue('art_bar'));
    }

    public function testBcGetValue(): void
    {
        $instance = new rex_article_content(1, 1);

        $viaSql = new ReflectionProperty(rex_article_content::class, 'viasql');
        $viaSql->setValue($instance, true);

        // fake meta field in database structure
        $propArticle = new ReflectionProperty(rex_article_content_base::class, 'ARTICLE');
        $propArticle->setValue($instance, rex_sql::factory()->setValue('art_foo', 'teststring'));

        static::assertEquals('teststring', $instance->getValue('foo'));
        static::assertEquals('teststring', $instance->getValue('art_foo'));
    }

    #[DataProvider('dataBcGetValueNonExisting')]
    public function testBcGetValueNonExisting(string $value): void
    {
        $instance = new rex_article_content(1, 1);

        $viaSql = new ReflectionProperty(rex_article_content::class, 'viasql');
        $viaSql->setValue($instance, true);

        $this->expectException(rex_exception::class);

        $instance->getValue($value);
    }

    /** @return list<array{string}> */
    public static function dataBcGetValueNonExisting(): array
    {
        return [
            ['bar'],
            ['art_bar'],
        ];
    }

    public function testHasValue(): void
    {
        $instance = new rex_article_content(1, 1);

        static::assertTrue($instance->hasValue('foo'));
        static::assertTrue($instance->hasValue('art_foo'));

        static::assertFalse($instance->hasValue('bar'));
        static::assertFalse($instance->hasValue('art_bar'));
    }

    public function testGetValue(): void
    {
        $instance = new rex_article_content(1, 1);

        static::assertEquals('teststring', $instance->getValue('foo'));
        static::assertEquals('teststring', $instance->getValue('art_foo'));
    }

    #[DataProvider('dataGetValueNonExisting')]
    public function testGetValueNonExisting(string $value): void
    {
        $instance = new rex_article_content(1, 1);

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
}
