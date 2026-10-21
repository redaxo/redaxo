<?php

namespace Redaxo\Core\Tests\Content;

use Override;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Redaxo\Core\Content\ArticleCache;
use Redaxo\Core\Content\ArticleContent;
use Redaxo\Core\Content\ContentHandler;
use Redaxo\Core\Content\ExtensionPoint\SliceOutput;
use Redaxo\Core\Database\Sql;
use Redaxo\Core\ExtensionPoint\Extension;
use ReflectionProperty;

/** @internal */
final class ArticleContentBaseTest extends TestCase
{
    private int $articleId;

    private int $sliceId;

    private mixed $extensions;

    #[Override]
    protected function setUp(): void
    {
        $this->articleId = Sql::factory()
            ->setTable('rex_article')
            ->setValues(['parent_id' => null, 'priority' => 1, 'path' => '|'])
            ->addGlobalCreateFields()
            ->addGlobalUpdateFields()
            ->insert()
            ->getLastId();

        Sql::factory()
            ->setTable('rex_article_translation')
            ->setValues(['article_id' => $this->articleId, 'language_id' => 1, 'name' => 'test', 'status' => 1])
            ->insert();

        $this->sliceId = ContentHandler::addSlice($this->articleId, 1, 1, 'testmodule1')->id;

        $this->extensions = new ReflectionProperty(Extension::class, 'extensions')->getValue();
    }

    #[Override]
    protected function tearDown(): void
    {
        new ReflectionProperty(Extension::class, 'extensions')->setValue(null, $this->extensions);

        ArticleCache::delete($this->articleId);

        // the translation and the slices are removed by the foreign keys
        Sql::factory()
            ->setTable('rex_article')
            ->setWhere(['id' => $this->articleId])
            ->delete();
    }

    #[DataProvider('provideSliceOutput')]
    public function testSliceOutput(string $expected, bool $eval): void
    {
        // the cache is generated before the extension is registered, so it must be applied at runtime
        ContentHandler::generateArticleContent($this->articleId, 1);

        Extension::register(SliceOutput::class, function (SliceOutput $ep): string {
            self::assertSame($this->sliceId, $ep->slice->id);

            return '[' . $ep->subject . ']';
        });

        $content = new ArticleContent($this->articleId, 1);
        $content->eval = $eval;

        self::assertSame($expected, $content->renderContent());
    }

    /** @return iterable<string, array{string, bool}> */
    public static function provideSliceOutput(): iterable
    {
        yield 'cached' => ['[output]', false];
        yield 'eval' => ['[output]', true];
    }
}
