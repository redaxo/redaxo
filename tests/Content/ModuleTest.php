<?php

namespace Redaxo\Core\Tests\Content;

use Override;
use PHPUnit\Framework\TestCase;
use Redaxo\Core\Content\ArticleCache;
use Redaxo\Core\Content\ArticleContent;
use Redaxo\Core\Content\ArticleSlice;
use Redaxo\Core\Content\ContentHandler;
use Redaxo\Core\Content\Module;
use Redaxo\Core\Database\Sql;
use Redaxo\Core\Exception\LogicException;
use Redaxo\Core\Fixtures\TestModule1;

/** @internal */
final class ModuleTest extends TestCase
{
    private const int FAKE_ID = 2_147_483_647;

    #[Override]
    protected function setUp(): void
    {
        // the slices need an article to hang off, see the foreign key of `rex_article_slice`
        Sql::factory()
            ->setTable('rex_article')
            ->setValues(['id' => self::FAKE_ID, 'parent_id' => null, 'priority' => 1, 'path' => '|'])
            ->addGlobalCreateFields()
            ->addGlobalUpdateFields()
            ->insert();

        Sql::factory()
            ->setTable('rex_article_translation')
            ->setValues(['article_id' => self::FAKE_ID, 'language_id' => 1, 'name' => 'test', 'status' => 1])
            ->insert();
    }

    #[Override]
    protected function tearDown(): void
    {
        // the translation and the slices are removed by the foreign keys
        Sql::factory()
            ->setTable('rex_article')
            ->setWhere(['id' => self::FAKE_ID])
            ->delete();

        ArticleCache::delete(self::FAKE_ID);
    }

    public function testRegistry(): void
    {
        self::assertSame(TestModule1::class, Module::getAll()['testmodule1'] ?? null);
        self::assertSame(TestModule1::class, Module::getClass('testmodule1'));
        self::assertNull(Module::getClass('unknown'));
        self::assertTrue(Module::exists('testmodule1'));
        self::assertFalse(Module::exists('unknown'));

        self::assertSame('testmodule1', TestModule1::key());
        self::assertSame('Test Module 1', TestModule1::name());
    }

    public function testKeyOfUnregisteredClass(): void
    {
        $this->expectException(LogicException::class);

        Module::key();
    }

    public function testOf(): void
    {
        $slice = $this->createSlice('testmodule1');

        $module = Module::of($slice);
        self::assertInstanceOf(TestModule1::class, $module);
        self::assertSame($slice, $module->slice);

        self::assertSame($slice, TestModule1::of($slice)->slice);
    }

    public function testOfUnknownModule(): void
    {
        $this->expectException(LogicException::class);

        Module::of($this->createSlice('unknown'));
    }

    public function testOutputFromArticleCache(): void
    {
        $this->createSlice('testmodule1');
        $this->createSlice('unknown');

        ContentHandler::generateArticleContent(self::FAKE_ID, 1);

        self::assertSame('output', new ArticleContent(self::FAKE_ID, 1)->renderContent());
    }

    private function createSlice(string $moduleKey): ArticleSlice
    {
        $sql = Sql::factory()
            ->setTable('rex_article_slice')
            ->setValues([
                'article_id' => self::FAKE_ID,
                'language_id' => 1,
                'ctype_id' => 1,
                'module' => $moduleKey,
                'revision' => 0,
                'priority' => 1,
            ])
            ->addGlobalCreateFields()
            ->addGlobalUpdateFields();
        $sql->insert();

        $slice = ArticleSlice::getArticleSliceById($sql->getLastId(), 1);
        self::assertNotNull($slice);

        return $slice;
    }
}
