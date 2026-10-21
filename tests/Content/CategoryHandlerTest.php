<?php

namespace Redaxo\Core\Tests\Content;

use Override;
use PHPUnit\Framework\TestCase;
use Redaxo\Core\Content\ArticleHandler;
use Redaxo\Core\Content\CategoryHandler;
use Redaxo\Core\Database\Sql;
use Redaxo\Core\Exception\InvalidArgumentException;

/** @internal */
final class CategoryHandlerTest extends TestCase
{
    /** @var list<int> */
    private array $categoryIds = [];

    #[Override]
    protected function tearDown(): void
    {
        foreach (array_reverse($this->categoryIds) as $id) {
            CategoryHandler::delete($id);
        }
    }

    public function testAdd(): void
    {
        $first = CategoryHandler::add(['parent_id' => null, 'name' => 'first']);
        $this->categoryIds[] = $first->id;
        $second = CategoryHandler::add(['parent_id' => null, 'name' => 'second']);
        $this->categoryIds[] = $second->id;

        self::assertSame($first->priority + 1, $second->priority);
        self::assertSame('second', $second->name);
        self::assertSame('second', $second->getStartArticle()->name);

        $child = CategoryHandler::add(['parent_id' => $second->id, 'name' => 'child', 'priority' => 1]);
        $this->categoryIds[] = $child->id;

        self::assertSame($second->id, $child->parentId);
        self::assertSame(1, $child->priority);
    }

    public function testAddRequiresParentId(): void
    {
        $this->expectException(InvalidArgumentException::class);

        /** @psalm-suppress InvalidArgument */
        CategoryHandler::add(['name' => 'foo']); // @phpstan-ignore argument.type
    }

    public function testEditKeepsMissingValues(): void
    {
        $first = CategoryHandler::add(['parent_id' => null, 'name' => 'first']);
        $this->categoryIds[] = $first->id;
        $second = CategoryHandler::add(['parent_id' => null, 'name' => 'second']);
        $this->categoryIds[] = $second->id;

        self::backdate();
        $category = CategoryHandler::edit($second->id, 1, ['priority' => $first->priority]);

        self::assertSame('second', $category->name);
        self::assertSame($first->priority, $category->priority);

        $category = CategoryHandler::edit($second->id, 1, ['name' => 'renamed']);

        self::assertSame('renamed', $category->name);
        self::assertSame($first->priority, $category->priority);
    }

    public function testArticleAddAndEdit(): void
    {
        $category = CategoryHandler::add(['parent_id' => null, 'name' => 'category']);
        $this->categoryIds[] = $category->id;

        $first = ArticleHandler::add(['category_id' => $category->id, 'name' => 'first']);
        $second = ArticleHandler::add(['category_id' => $category->id, 'name' => 'second']);

        // the start article comes first
        self::assertSame(2, $first->priority);
        self::assertSame(3, $second->priority);

        self::backdate();
        $article = ArticleHandler::edit($second->id, 1, ['priority' => 2]);

        self::assertSame('second', $article->name);
        self::assertSame(2, $article->priority);

        ArticleHandler::delete($first->id);
        ArticleHandler::delete($second->id);
    }

    /** The priorities are reorganized by updatedate on ties, so the edited element must not share the current second. */
    private static function backdate(): void
    {
        Sql::factory()->setQuery('UPDATE rex_article SET updatedate = updatedate - INTERVAL 1 MINUTE');
        Sql::factory()->setQuery('UPDATE rex_category SET updatedate = updatedate - INTERVAL 1 MINUTE');
    }
}
