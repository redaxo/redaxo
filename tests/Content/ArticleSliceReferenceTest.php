<?php

namespace Redaxo\Core\Tests\Content;

use Override;
use PHPUnit\Framework\TestCase;
use Redaxo\Core\Content\ArticleHandler;
use Redaxo\Core\Content\ArticleRevision;
use Redaxo\Core\Content\ArticleSliceReference;
use Redaxo\Core\Content\ContentHandler;
use Redaxo\Core\Content\ExtensionPoint\ArticleUsageCheck;
use Redaxo\Core\Database\Sql;
use Redaxo\Core\Exception\UserMessageException;
use Redaxo\Core\ExtensionPoint\Extension;
use Redaxo\Core\Form\Reference;
use Redaxo\Core\Util\Type;
use ReflectionProperty;

/** @internal */
final class ArticleSliceReferenceTest extends TestCase
{
    private const int ARTICLE = 2_147_483_646;
    private const int LINKED = 2_147_483_647;

    #[Override]
    protected function setUp(): void
    {
        foreach ([self::ARTICLE, self::LINKED] as $id) {
            Sql::factory()
                ->setTable('rex_article')
                ->setValues(['id' => $id, 'parent_id' => null, 'priority' => 1, 'path' => '|'])
                ->addGlobalCreateFields()
                ->addGlobalUpdateFields()
                ->insert();

            Sql::factory()
                ->setTable('rex_article_translation')
                ->setValues(['article_id' => $id, 'language_id' => 1, 'name' => 'test ' . $id, 'status' => 1])
                ->insert();
        }
    }

    #[Override]
    protected function tearDown(): void
    {
        // the translations, the slices and their references are removed by the foreign keys
        Sql::factory()->setQuery('DELETE FROM rex_article WHERE id IN (?, ?)', [self::ARTICLE, self::LINKED]);
    }

    public function testReferencesFollowTheSlices(): void
    {
        $slice = ContentHandler::addSlice(self::ARTICLE, 1, 1, 'test_fields', ['headline' => 'foo', 'link' => self::LINKED]);
        self::assertSame([self::ARTICLE], $this->findLinkingArticles());

        ContentHandler::copyContent(self::ARTICLE, self::LINKED);
        self::assertSame([self::ARTICLE, self::LINKED], $this->findLinkingArticles());

        ArticleRevision::copyContent(self::ARTICLE, 1, ArticleRevision::LIVE, ArticleRevision::WORK);
        self::assertSame(2, $this->countReferences(self::ARTICLE));

        ContentHandler::deleteSlice($slice->id);
        self::assertSame(1, $this->countReferences(self::ARTICLE));
    }

    public function testReferencesOfCollectionItems(): void
    {
        ContentHandler::addSlice(self::ARTICLE, 1, 1, 'test_fields', ['headline' => 'foo', 'items' => [['title' => 'a'], ['title' => 'b', 'link' => self::LINKED]]]);

        self::assertSame([self::ARTICLE], $this->findLinkingArticles());
    }

    public function testFieldsWithoutReferences(): void
    {
        ContentHandler::addSlice(self::ARTICLE, 1, 1, 'test_fields', ['headline' => 'foo']);
        ContentHandler::addSlice(self::ARTICLE, 1, 1, 'unknown', ['link' => self::LINKED]);

        self::assertSame(0, $this->countReferences(self::ARTICLE));
    }

    public function testRebuild(): void
    {
        ContentHandler::addSlice(self::ARTICLE, 1, 1, 'test_fields', ['headline' => 'foo', 'link' => self::LINKED]);
        Sql::factory()->setQuery('DELETE r FROM rex_article_slice_reference r JOIN rex_article_slice s ON s.id = r.slice_id WHERE s.article_id = ?', [self::ARTICLE]);

        ArticleSliceReference::rebuild();

        self::assertSame([self::ARTICLE], $this->findLinkingArticles());
    }

    public function testDeletingAReferencedArticleFails(): void
    {
        ContentHandler::addSlice(self::ARTICLE, 1, 1, 'test_fields', ['headline' => 'foo', 'link' => self::LINKED]);

        $this->expectException(UserMessageException::class);
        $this->expectExceptionMessage('test ' . self::ARTICLE);

        ArticleHandler::delete(self::LINKED);
    }

    public function testDeletingAnArticleReferencingItself(): void
    {
        ContentHandler::addSlice(self::LINKED, 1, 1, 'test_fields', ['headline' => 'foo', 'link' => self::LINKED]);

        ArticleHandler::delete(self::LINKED);

        self::assertSame([], $this->findLinkingArticles());
    }

    public function testExtensionsCanReportUsages(): void
    {
        $extensions = Type::array(new ReflectionProperty(Extension::class, 'extensions')->getValue());

        try {
            Extension::register(ArticleUsageCheck::class, static function (ArticleUsageCheck $ep): void {
                $ep->usages->add('Foo', 'used by foo ' . $ep->article->id);
            });

            $this->expectException(UserMessageException::class);
            $this->expectExceptionMessage('used by foo ' . self::LINKED);

            ArticleHandler::delete(self::LINKED);
        } finally {
            new ReflectionProperty(Extension::class, 'extensions')->setValue(null, $extensions);
        }
    }

    /** @return list<int> */
    private function findLinkingArticles(): array
    {
        return array_column(ArticleSliceReference::findArticles(Reference::article(self::LINKED)), 'articleId');
    }

    private function countReferences(int $articleId): int
    {
        return (int) Sql::factory()
            ->setQuery('SELECT COUNT(*) AS c FROM rex_article_slice_reference r JOIN rex_article_slice s ON s.id = r.slice_id WHERE s.article_id = ?', [$articleId])
            ->getValue('c');
    }
}
