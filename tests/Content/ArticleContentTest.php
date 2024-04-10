<?php

namespace Redaxo\Core\Tests\Content;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Redaxo\Core\Content\Article;
use Redaxo\Core\Content\ArticleContent;
use Redaxo\Core\Content\ArticleContentBase;
use Redaxo\Core\Database\Sql;
use Redaxo\Core\Filesystem\Dir;
use Redaxo\Core\Filesystem\File;
use Redaxo\Core\Filesystem\Finder;
use Redaxo\Core\Filesystem\Path;
use ReflectionClass;
use ReflectionProperty;
use rex_exception;

/** @internal */
final class ArticleContentTest extends TestCase
{
    protected function setUp(): void
    {
        // fake article
        $articleFile = Path::coreCache('structure/1.1.article');
        File::putCache($articleFile, [
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
        // delete all fake structure cache files
        $finder = Finder::factory(Path::coreCache('structure/'))
            ->recursive()
            ->childFirst()
            ->ignoreSystemStuff(false);
        Dir::deleteIterator($finder);

        // reset static properties
        $class = new ReflectionClass(Article::class);
        $class->setStaticPropertyValue('classVars', null);

        Article::clearInstancePool();
    }

    public function testBcHasValue(): void
    {
        $instance = new ArticleContent(1, 1);
        $viaSql = new ReflectionProperty(ArticleContent::class, 'viasql');

        $viaSql->setValue($instance, true);

        // fake meta field in database structure
        $propArticle = new ReflectionProperty(ArticleContentBase::class, 'ARTICLE');
        $propArticle->setValue($instance, Sql::factory()->setValue('art_foo', 'teststring'));

        self::assertTrue($instance->hasValue('foo'));
        self::assertTrue($instance->hasValue('art_foo'));

        self::assertFalse($instance->hasValue('bar'));
        self::assertFalse($instance->hasValue('art_bar'));
    }

    public function testBcGetValue(): void
    {
        $instance = new ArticleContent(1, 1);

        $viaSql = new ReflectionProperty(ArticleContent::class, 'viasql');
        $viaSql->setValue($instance, true);

        // fake meta field in database structure
        $propArticle = new ReflectionProperty(ArticleContentBase::class, 'ARTICLE');
        $propArticle->setValue($instance, Sql::factory()->setValue('art_foo', 'teststring'));

        self::assertEquals('teststring', $instance->getValue('foo'));
        self::assertEquals('teststring', $instance->getValue('art_foo'));
    }

    #[DataProvider('dataBcGetValueNonExisting')]
    public function testBcGetValueNonExisting(string $value): void
    {
        $instance = new ArticleContent(1, 1);

        $viaSql = new ReflectionProperty(ArticleContent::class, 'viasql');
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
        $instance = new ArticleContent(1, 1);

        self::assertTrue($instance->hasValue('foo'));
        self::assertTrue($instance->hasValue('art_foo'));

        self::assertFalse($instance->hasValue('bar'));
        self::assertFalse($instance->hasValue('art_bar'));
    }

    public function testGetValue(): void
    {
        $instance = new ArticleContent(1, 1);

        self::assertEquals('teststring', $instance->getValue('foo'));
        self::assertEquals('teststring', $instance->getValue('art_foo'));
    }

    #[DataProvider('dataGetValueNonExisting')]
    public function testGetValueNonExisting(string $value): void
    {
        $instance = new ArticleContent(1, 1);

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
