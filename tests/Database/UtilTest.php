<?php

namespace Redaxo\Core\Tests\Database;

use Override;
use PHPUnit\Framework\TestCase;
use Redaxo\Core\Database\Column;
use Redaxo\Core\Database\Index;
use Redaxo\Core\Database\Sql;
use Redaxo\Core\Database\Table;
use Redaxo\Core\Database\Util;

/** @internal */
final class UtilTest extends TestCase
{
    public const string TABLE = 'rex_sql_util_test';
    public const string TABLE2 = 'rex_sql_util_test2';

    #[Override]
    protected function tearDown(): void
    {
        $sql = Sql::factory();
        $sql->setQuery('DROP TABLE IF EXISTS `' . self::TABLE2 . '`');
        $sql->setQuery('DROP TABLE IF EXISTS `' . self::TABLE . '`');

        Table::clearInstancePool();
    }

    private function createTableWithData(): Table
    {
        $table = Table::get(self::TABLE);
        $table
            ->ensurePrimaryIdColumn()
            ->ensureColumn(new Column('title', 'varchar(255)'))
            ->ensureIndex(new Index('i_title', ['title']))
            ->create();

        $sql = Sql::factory();
        for ($i = 1; $i < 3; ++$i) {
            $sql
                ->setTable(self::TABLE)
                ->setValue('title', 'Title ' . $i)
                ->insert();
        }

        return $table;
    }

    public function testCopyTable(): void
    {
        $table = self::createTableWithData();

        Util::copyTable(self::TABLE, self::TABLE2);

        $table2 = Table::get(self::TABLE2);

        self::assertEquals($table2->getColumns(), $table->getColumns());
        self::assertEquals($table2->getIndexes(), $table->getIndexes());

        self::assertSame(0, Sql::factory()->setTable(self::TABLE2)->select()->getRows());
    }

    public function testCopyTableWithData(): void
    {
        $table = self::createTableWithData();

        Util::copyTableWithData(self::TABLE, self::TABLE2);

        $table2 = Table::get(self::TABLE2);

        self::assertEquals($table2->getColumns(), $table->getColumns());
        self::assertEquals($table2->getIndexes(), $table->getIndexes());

        self::assertSame(2, Sql::factory()->setTable(self::TABLE2)->select()->getRows());
    }
}
