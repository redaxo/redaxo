<?php

namespace Redaxo\Core\Tests\Content;

use Override;
use PHPUnit\Framework\TestCase;
use Redaxo\Core\Content\ArticleSlice;
use Redaxo\Core\Content\ContentHandler;
use Redaxo\Core\Content\ExtensionPoint\ArticleContentUpdated;
use Redaxo\Core\Database\Sql;
use Redaxo\Core\ExtensionPoint\Extension;
use ReflectionProperty;

/** @internal */
final class ContentHandlerTest extends TestCase
{
    private int $articleId;

    private mixed $extensions;

    /** @var list<ArticleContentUpdated> */
    private array $events = [];

    #[Override]
    protected function setUp(): void
    {
        // the slices need an article to hang off, see the foreign key of `rex_article_slice`
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

        $this->extensions = new ReflectionProperty(Extension::class, 'extensions')->getValue();

        Extension::register(ArticleContentUpdated::NAME, function (ArticleContentUpdated $ep): void {
            $this->events[] = $ep;
        });
    }

    #[Override]
    protected function tearDown(): void
    {
        new ReflectionProperty(Extension::class, 'extensions')->setValue(null, $this->extensions);

        // the translation and the slices are removed by the foreign keys
        Sql::factory()
            ->setTable('rex_article')
            ->setWhere(['id' => $this->articleId])
            ->delete();
    }

    public function testSliceActionsDispatchArticleContentUpdatedWithSlice(): void
    {
        ContentHandler::addSlice($this->articleId, 1, 1, 'test', ['value1' => 'foo']);
        ContentHandler::addSlice($this->articleId, 1, 1, 'test', ['value1' => 'bar']);

        $first = $this->events[0]->slice;
        self::assertNotNull($first);
        self::assertSame('foo', $first->getValue(1));
        self::assertSame(1, $first->priority);

        // moveSlice() resolves equal priorities by updatedate, so the slices must not share the current second
        Sql::factory()->setQuery('UPDATE rex_article_slice SET updatedate = updatedate - INTERVAL 1 MINUTE WHERE article_id = ?', [$this->articleId]);

        ContentHandler::moveSlice($first->id, 1, 'movedown');
        ContentHandler::sliceStatus($first->id, 0);
        ContentHandler::deleteSlice($first->id);

        self::assertSame(
            ['slice_added', 'slice_added', 'slice_moved', 'slice_status', 'slice_deleted'],
            array_map(static fn (ArticleContentUpdated $ep) => $ep->action, $this->events),
        );

        [, $second, $moved, $status, $deleted] = array_map(static fn (ArticleContentUpdated $ep) => $ep->slice, $this->events);

        self::assertSame('bar', $second?->getValue(1));
        self::assertNotNull($moved);
        self::assertSame(2, $moved->priority);
        self::assertSame(1, $moved->status);
        self::assertSame(0, $status?->status);
        self::assertSame($first->id, $deleted?->id);
        self::assertNull(ArticleSlice::getArticleSliceById($first->id, 1));
    }
}
