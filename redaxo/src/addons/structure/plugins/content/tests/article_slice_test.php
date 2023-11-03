<?php

use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
class rex_article_slice_test extends TestCase
{
    private const FAKE_ID = 2_147_483_647; // max int on 32bit

    protected function tearDown(): void
    {
        rex_sql::factory()
            ->setTable(rex::getTable('article_slice'))
            ->setWhere(['article_id' => self::FAKE_ID])
            ->delete();
    }

    public function testGetValue(): void
    {
        $id = $this->addSlice(1, 1);

        $slice = rex_article_slice::getArticleSliceById($id);

        static::assertNotNull($slice);
        static::assertSame('foo', $slice->getValue(1));
        static::assertNull($slice->getValue(2));
        static::assertSame(1, $slice->getValue('priority'));
    }

    public function testGetNextSlice(): void
    {
        $id = $this->addSlice(1, 1);
        $next = $this->addSlice(2, 0);
        $this->addSlice(4, 1);
        $nextOnline = $this->addSlice(3, 1);
        $this->addSlice(5, 1);

        $slice = rex_article_slice::getArticleSliceById($id);

        static::assertSame($next, $slice->getNextSlice()->getId());
        static::assertSame($nextOnline, $slice->getNextSlice(true)->getId());
    }

    public function testGetPreviousSlice(): void
    {
        $this->addSlice(1, 1);
        $previousOnline = $this->addSlice(3, 1);
        $this->addSlice(2, 1);
        $previous = $this->addSlice(4, 0);
        $id = $this->addSlice(5, 1);

        $slice = rex_article_slice::getArticleSliceById($id);

        static::assertSame($previous, $slice->getPreviousSlice()->getId());
        static::assertSame($previousOnline, $slice->getPreviousSlice(true)->getId());
    }

    private function addSlice(int $priority, int $status): int
    {
        $sql = rex_sql::factory();
        $sql
            ->setTable(rex::getTable('article_slice'))
            ->setValues([
                'article_id' => self::FAKE_ID,
                'clang_id' => 1,
                'ctype_id' => 1,
                'module_id' => self::FAKE_ID,
                'revision' => 0,
                'priority' => $priority,
                'status' => $status,
                'value1' => 'foo',
            ])
            ->insert();

        return (int) $sql->getLastId();
    }
}
