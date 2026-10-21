<?php

namespace Redaxo\Core\Tests\MediaPool;

use Override;
use PHPUnit\Framework\TestCase;
use Redaxo\Core\Exception\UserMessageException;
use Redaxo\Core\MediaPool\MediaCategoryHandler;

/** @internal */
final class MediaCategoryHandlerTest extends TestCase
{
    /** @var list<int> */
    private array $categoryIds = [];

    #[Override]
    protected function tearDown(): void
    {
        foreach (array_reverse($this->categoryIds) as $id) {
            MediaCategoryHandler::delete($id);
        }
    }

    public function testAddAndEdit(): void
    {
        $parent = MediaCategoryHandler::add(['parent_id' => null, 'name' => 'parent']);
        $this->categoryIds[] = $parent->id;
        $child = MediaCategoryHandler::add(['parent_id' => $parent->id, 'name' => 'child']);
        $this->categoryIds[] = $child->id;

        self::assertNull($parent->parentId);
        self::assertSame([], $parent->path);
        self::assertSame($parent->id, $child->parentId);
        self::assertSame([$parent->id], $child->path);

        $child = MediaCategoryHandler::edit($child->id, ['name' => 'renamed']);

        self::assertSame('renamed', $child->name);
        self::assertSame($parent->id, $child->parentId);
    }

    public function testAddThrowsForUnknownParent(): void
    {
        $this->expectException(UserMessageException::class);

        MediaCategoryHandler::add(['parent_id' => 999_999, 'name' => 'child']);
    }
}
