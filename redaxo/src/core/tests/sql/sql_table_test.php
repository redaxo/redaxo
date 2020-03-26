<?php

use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
class rex_sql_table_test extends TestCase
{
    public const TABLE = 'rex_sql_table_test';
    public const TABLE2 = 'rex_sql_table_test2';

    protected function tearDown()
    {
        $sql = rex_sql::factory();
        $sql->setQuery('DROP TABLE IF EXISTS `' . self::TABLE2 . '`');
        $sql->setQuery('DROP TABLE IF EXISTS `' . self::TABLE . '`');

        rex_sql_table::clearInstancePool();
    }

    protected function createTable()
    {
        $table = rex_sql_table::get(self::TABLE);

        $table
            ->addColumn(new rex_sql_column('id', 'int(11)', false, null, 'auto_increment'))
            ->addColumn(new rex_sql_column('title', 'varchar(255)', true, 'Default title'))
            ->setPrimaryKey('id')
            ->addIndex(new rex_sql_index('i_title', ['title']))
            ->create();

        return $table;
    }

    protected function createTable2()
    {
        $table = rex_sql_table::get(self::TABLE2);

        $table
            ->addColumn(new rex_sql_column('id', 'int(11)', false, null, 'auto_increment'))
            ->addColumn(new rex_sql_column('test1_id', 'int(11)'))
            ->setPrimaryKey('id')
            ->addForeignKey(new rex_sql_foreign_key('test2_fk_test1', self::TABLE, ['test1_id' => 'id']))
            ->create();

        return $table;
    }

    public function testCreate()
    {
        static::assertFalse(rex_sql_table::get(self::TABLE)->exists());

        static::assertTrue($this->createTable()->exists());

        rex_sql_table::clearInstance(self::TABLE);
        $table = rex_sql_table::get(self::TABLE);

        static::assertTrue($table->exists());
        static::assertSame(self::TABLE, $table->getName());

        static::assertCount(2, $table->getColumns());
        static::assertTrue($table->hasColumn('id'));
        static::assertTrue($table->hasColumn('title'));
        static::assertFalse($table->hasColumn('foo'));
        static::assertSame(['id'], $table->getPrimaryKey());

        $id = $table->getColumn('id');

        static::assertSame('id', $id->getName());
        static::assertSame('int(11)', $id->getType());
        static::assertFalse($id->isNullable());
        static::assertNull($id->getDefault());
        static::assertSame('auto_increment', $id->getExtra());

        $title = $table->getColumn('title');

        static::assertSame('title', $title->getName());
        static::assertSame('varchar(255)', $title->getType());
        static::assertTrue($title->isNullable());
        static::assertSame('Default title', $title->getDefault());
        static::assertNull($title->getExtra());

        static::assertCount(1, $table->getIndexes());
        static::assertTrue($table->hasIndex('i_title'));
        static::assertFalse($table->hasIndex('i_foo'));

        $index = $table->getIndex('i_title');

        static::assertSame('i_title', $index->getName());
        static::assertSame(rex_sql_index::INDEX, $index->getType());
        static::assertSame(['title'], $index->getColumns());

        static::assertTrue($this->createTable2()->exists());

        rex_sql_table::clearInstance(self::TABLE2);
        $table2 = rex_sql_table::get(self::TABLE2);

        static::assertCount(1, $table2->getForeignKeys());
        $a = $table2->hasForeignKey('test2_fk_test1');
        static::assertTrue($table2->hasForeignKey('test2_fk_test1'));
        static::assertFalse($table2->hasForeignKey('foo'));

        $fk = $table2->getForeignKey('test2_fk_test1');

        static::assertSame('test2_fk_test1', $fk->getName());
        static::assertSame(self::TABLE, $fk->getTable());
        static::assertSame(rex_sql_foreign_key::RESTRICT, $fk->getOnUpdate());
        static::assertSame(rex_sql_foreign_key::RESTRICT, $fk->getOnDelete());
        static::assertSame(['test1_id' => 'id'], $fk->getColumns());
    }

    public function testDrop()
    {
        $table = $this->createTable();

        $table->drop();

        static::assertFalse($table->exists());

        rex_sql_table::clearInstance(self::TABLE);
        $table = rex_sql_table::get(self::TABLE);

        static::assertFalse($table->exists());

        $table->drop();
    }

    public function testSetName()
    {
        $table = $this->createTable();

        $table
            ->setName(self::TABLE2)
            ->alter();

        static::assertFalse(rex_sql_table::get(self::TABLE)->exists());

        rex_sql_table::clearInstance(self::TABLE2);
        $table = rex_sql_table::get(self::TABLE2);

        static::assertTrue($table->exists());
    }

    public function testAddColumn()
    {
        $table = $this->createTable();

        $description = new rex_sql_column('description', 'text', true);
        $table
            ->addColumn($description)
            ->addColumn(new rex_sql_column('name', 'varchar(255)'), 'id')
            ->addColumn(new rex_sql_column('pid', 'int(11)'), rex_sql_table::FIRST)
            ->alter();

        static::assertSame($description, $table->getColumn('description'));

        rex_sql_table::clearInstance(self::TABLE);
        $table = rex_sql_table::get(self::TABLE);

        static::assertEquals($description, $table->getColumn('description'));

        static::assertSame(['pid', 'id', 'name', 'title', 'description'], array_keys($table->getColumns()));
    }

    public function testEnsureColumn()
    {
        $table = $this->createTable();

        $title = new rex_sql_column('title', 'varchar(20)', false);
        $description = new rex_sql_column('description', 'text', true);
        $table
            ->ensureColumn($description)
            ->ensureColumn($title, 'description')
            ->alter();

        static::assertSame($title, $table->getColumn('title'));
        static::assertSame($description, $table->getColumn('description'));

        rex_sql_table::clearInstance(self::TABLE);
        $table = rex_sql_table::get(self::TABLE);

        static::assertEquals($title, $table->getColumn('title'));
        static::assertEquals($description, $table->getColumn('description'));

        static::assertSame(['id', 'description', 'title'], array_keys($table->getColumns()));

        $amount = new rex_sql_column('amount', 'int(5)', true);

        $table
            ->ensureColumn($title, 'id')
            ->ensureColumn(new rex_sql_column('status', 'tinyint(1)'), 'id')
            ->ensureColumn(new rex_sql_column('created', 'datetime', false, 'CURRENT_TIMESTAMP'), 'status')
            ->ensureColumn($title, 'status')
            ->ensureColumn($amount)
            ->alter();

        $expectedOrder = ['id', 'status', 'title', 'created', 'description', 'amount'];

        static::assertSame($expectedOrder, array_keys($table->getColumns()));

        rex_sql_table::clearInstance(self::TABLE);
        $table = rex_sql_table::get(self::TABLE);

        static::assertSame($expectedOrder, array_keys($table->getColumns()));

        $sql = rex_sql::factory();
        if (rex_sql::MYSQL === $sql->getDbType() && 8 <= (int) $sql->getDbVersion()) {
            // In MySQL 8 the display width of integers is simulated by rex_sql_table to the max width.
            static::assertEquals('int(11)', $table->getColumn('amount')->getType());
        } else {
            static::assertEquals('int(5)', $table->getColumn('amount')->getType());
        }
    }

    public function testEnsurePrimaryIdColumn()
    {
        $table = rex_sql_table::get(self::TABLE);
        $table
            ->ensurePrimaryIdColumn()
            ->create();

        $id = $table->getColumn('id');
        static::assertSame('int(10) unsigned', $id->getType());
        static::assertFalse($id->isNullable());
        static::assertNull($id->getDefault());
        static::assertSame('auto_increment', $id->getExtra());
        static::assertSame(['id'], $table->getPrimaryKey());
    }

    public function testEnsureGlobalColumns()
    {
        $table = $this->createTable();
        $table
            ->ensureGlobalColumns()
            ->alter();

        static::assertTrue($table->hasColumn('createdate'));
        static::assertSame('datetime', $table->getColumn('createdate')->getType());
        static::assertTrue($table->hasColumn('createuser'));
        static::assertSame('varchar(255)', $table->getColumn('createuser')->getType());
        static::assertTrue($table->hasColumn('updatedate'));
        static::assertSame('datetime', $table->getColumn('updatedate')->getType());
        static::assertTrue($table->hasColumn('updateuser'));
        static::assertSame('varchar(255)', $table->getColumn('updateuser')->getType());
    }

    public function testRenameColumn()
    {
        $table = $this->createTable();

        $table->renameColumn('title', 'name');

        static::assertFalse($table->hasColumn('title'));
        static::assertTrue($table->hasColumn('name'));

        $table->alter();

        static::assertTrue($table->hasColumn('name'));

        rex_sql_table::clearInstance(self::TABLE);
        $table = rex_sql_table::get(self::TABLE);

        static::assertFalse($table->hasColumn('title'));
        static::assertTrue($table->hasColumn('name'));
        static::assertSame('varchar(255)', $table->getColumn('name')->getType());

        $table
            ->renameColumn('id', 'pid')
            ->alter();

        static::assertSame(['pid'], $table->getPrimaryKey());

        rex_sql_table::clearInstance(self::TABLE);
        $table = rex_sql_table::get(self::TABLE);

        static::assertSame(['pid'], $table->getPrimaryKey());
    }

    public function testRenameColumnNonExisting()
    {
        $this->expectException(\rex_exception::class);

        $table = $this->createTable();
        $table->renameColumn('foo', 'bar');
    }

    public function testRenameColumnToAlreadyExisting()
    {
        $this->expectException(\rex_exception::class);

        $table = $this->createTable();
        $table->renameColumn('id', 'title');
    }

    public function testRemoveColumn()
    {
        $table = $this->createTable();

        $table
            ->removeColumn('title')
            ->alter();

        static::assertFalse($table->hasColumn('title'));

        rex_sql_table::clearInstance(self::TABLE);
        $table = rex_sql_table::get(self::TABLE);

        static::assertFalse($table->hasColumn('title'));
    }

    public function testSetPrimaryKey()
    {
        $table = $this->createTable();

        $primaryKey = ['id', 'title'];
        $table
            ->setPrimaryKey($primaryKey)
            ->alter();

        static::assertSame($primaryKey, $table->getPrimaryKey());

        rex_sql_table::clearInstance(self::TABLE);
        $table = rex_sql_table::get(self::TABLE);

        static::assertSame($primaryKey, $table->getPrimaryKey());

        $table->getColumn('id')->setExtra(null);
        $table
            ->setPrimaryKey(null)
            ->alter();

        rex_sql_table::clearInstance(self::TABLE);
        $table = rex_sql_table::get(self::TABLE);

        static::assertNull($table->getPrimaryKey());

        $table
            ->setPrimaryKey('id')
            ->alter();

        rex_sql_table::clearInstance(self::TABLE);
        $table = rex_sql_table::get(self::TABLE);

        static::assertSame(['id'], $table->getPrimaryKey());
    }

    public function testAddIndex()
    {
        $table = $this->createTable();

        $uuid = new rex_sql_index('i_uuid', ['uuid'], rex_sql_index::UNIQUE);
        $description = new rex_sql_index('i_description', ['description'], rex_sql_index::FULLTEXT);
        $search = new rex_sql_index('i_search', ['title', 'description'], rex_sql_index::FULLTEXT);

        $table
            ->addColumn(new rex_sql_column('uuid', 'varchar(255)'))
            ->addColumn(new rex_sql_column('description', 'text', true))
            ->addIndex($uuid)
            ->addIndex($description)
            ->addIndex($search)
            ->alter();

        static::assertSame($uuid, $table->getIndex('i_uuid'));
        static::assertSame($description, $table->getIndex('i_description'));
        static::assertSame($search, $table->getIndex('i_search'));

        rex_sql_table::clearInstance(self::TABLE);
        $table = rex_sql_table::get(self::TABLE);

        static::assertEquals($uuid, $table->getIndex('i_uuid'));
        static::assertEquals($description, $table->getIndex('i_description'));
        static::assertEquals($search, $table->getIndex('i_search'));
    }

    public function testEnsureIndex()
    {
        $table = $this->createTable();

        $title = new rex_sql_index('i_title', ['title', 'title2'], rex_sql_index::UNIQUE);
        $title2 = new rex_sql_index('i_title2', ['title2']);
        $table
            ->ensureColumn(new rex_sql_column('title2', 'varchar(20)'))
            ->ensureIndex($title)
            ->ensureIndex($title2)
            ->alter();

        static::assertSame($title, $table->getIndex('i_title'));
        static::assertSame($title2, $table->getIndex('i_title2'));

        rex_sql_table::clearInstance(self::TABLE);
        $table = rex_sql_table::get(self::TABLE);

        static::assertEquals($title, $table->getIndex('i_title'));
        static::assertEquals($title2, $table->getIndex('i_title2'));
    }

    public function testRenameIndex()
    {
        $table = $this->createTable();

        $table->renameIndex('i_title', 'index_title');

        static::assertFalse($table->hasIndex('i_title'));
        static::assertTrue($table->hasIndex('index_title'));

        $table->alter();

        static::assertTrue($table->hasIndex('index_title'));

        rex_sql_table::clearInstance(self::TABLE);
        $table = rex_sql_table::get(self::TABLE);

        static::assertFalse($table->hasIndex('i_title'));
        static::assertTrue($table->hasIndex('index_title'));
        static::assertSame(['title'], $table->getIndex('index_title')->getColumns());
    }

    public function testRemoveIndex()
    {
        $table = $this->createTable();

        $table
            ->removeIndex('i_title')
            ->alter();

        static::assertFalse($table->hasColumn('i_title'));

        rex_sql_table::clearInstance(self::TABLE);
        $table = rex_sql_table::get(self::TABLE);

        static::assertFalse($table->hasColumn('i_title'));
    }

    public function testAddForeignKey()
    {
        $table = $this->createTable();

        $fk = new rex_sql_foreign_key('test1_fk_config', 'rex_config', [
            'config_namespace' => 'namespace',
            'config_key' => 'key',
        ], rex_sql_foreign_key::CASCADE, rex_sql_foreign_key::SET_NULL);

        $table
            ->addColumn(new rex_sql_column('config_namespace', 'varchar(75)', true))
            ->addColumn(new rex_sql_column('config_key', 'varchar(255)', true))
            ->addForeignKey($fk)
            ->alter();

        static::assertSame($fk, $table->getForeignKey('test1_fk_config'));

        rex_sql_table::clearInstance(self::TABLE);
        $table = rex_sql_table::get(self::TABLE);

        static::assertEquals($fk, $table->getForeignKey('test1_fk_config'));
    }

    public function testEnsureForeignKey()
    {
        $table = $this->createTable();
        $table2 = $this->createTable2();

        $fk1 = new rex_sql_foreign_key('test2_fk_test1', self::TABLE, [
            'test1_id' => 'id',
        ], rex_sql_foreign_key::RESTRICT, rex_sql_foreign_key::CASCADE);

        $fk2 = new rex_sql_foreign_key('test2_fk_config', 'rex_config', [
            'config_namespace' => 'namespace',
            'config_key' => 'key',
        ], rex_sql_foreign_key::CASCADE, rex_sql_foreign_key::SET_NULL);

        $table2
            ->ensureColumn(new rex_sql_column('config_namespace', 'varchar(75)', true))
            ->ensureColumn(new rex_sql_column('config_key', 'varchar(255)', true))
            ->ensureForeignKey($fk1)
            ->ensureForeignKey($fk2)
            ->alter();

        static::assertSame($fk1, $table2->getForeignKey('test2_fk_test1'));
        static::assertSame($fk2, $table2->getForeignKey('test2_fk_config'));

        rex_sql_table::clearInstance(self::TABLE2);
        $table2 = rex_sql_table::get(self::TABLE2);

        static::assertEquals($fk1, $table2->getForeignKey('test2_fk_test1'));
        static::assertEquals($fk2, $table2->getForeignKey('test2_fk_config'));
    }

    public function testRenameForeignKey()
    {
        $table = $this->createTable();
        $table2 = $this->createTable2();

        $table2->renameForeignKey('test2_fk_test1', 'fk_test2_test1');

        static::assertFalse($table2->hasForeignKey('test2_fk_test1'));
        static::assertTrue($table2->hasForeignKey('fk_test2_test1'));

        $table2->alter();

        static::assertTrue($table2->hasForeignKey('fk_test2_test1'));

        rex_sql_table::clearInstance(self::TABLE2);
        $table2 = rex_sql_table::get(self::TABLE2);

        static::assertFalse($table2->hasForeignKey('test2_fk_test1'));
        static::assertTrue($table2->hasForeignKey('fk_test2_test1'));
        static::assertSame(['test1_id' => 'id'], $table2->getForeignKey('fk_test2_test1')->getColumns());
    }

    public function testRemoveForeignKey()
    {
        $table = $this->createTable();
        $table2 = $this->createTable2();

        $table2
            ->removeForeignKey('test2_fk_test1')
            ->alter();

        static::assertFalse($table2->hasForeignKey('test2_fk_test1'));

        rex_sql_table::clearInstance(self::TABLE2);
        $table2 = rex_sql_table::get(self::TABLE2);

        static::assertFalse($table2->hasForeignKey('test2_fk_test1'));
    }

    public function testAlter()
    {
        $table = $this->createTable();

        $table->getColumn('id')->setType('int(10) unsigned');
        $table
            ->setName(self::TABLE2)
            ->removeColumn('title')
            ->addColumn(new rex_sql_column('name', 'varchar(20)'))
            ->setPrimaryKey(['id', 'name'])
            ->addIndex(new rex_sql_index('i_name', ['name']))
            ->alter();

        rex_sql_table::clearInstance(self::TABLE2);
        $table = rex_sql_table::get(self::TABLE2);

        static::assertFalse($table->hasColumn('title'));
        static::assertFalse($table->hasIndex('i_title'));
        static::assertTrue($table->hasColumn('name'));
        static::assertTrue($table->hasIndex('i_name'));
        static::assertSame('int(10) unsigned', $table->getColumn('id')->getType());
        static::assertEquals(['id', 'name'], $table->getPrimaryKey());
        static::assertEquals(['name'], $table->getIndex('i_name')->getColumns());
    }

    public function testEnsure()
    {
        $table = rex_sql_table::get(self::TABLE);
        $table
            ->ensureColumn(new rex_sql_column('title', 'varchar(255)', false, 'Default title'))
            ->ensureColumn(new rex_sql_column('teaser', 'varchar(255)', false))
            ->ensureColumn(new rex_sql_column('id', 'int(11)', false, null, 'auto_increment'), rex_sql_table::FIRST)
            ->ensureColumn(new rex_sql_column('status', 'tinyint(1)'))
            ->ensureColumn(new rex_sql_column('timestamp', 'datetime', true))
            ->ensureColumn(new rex_sql_column('description', 'text', true), 'title')
            ->setPrimaryKey('id')
            ->ensureIndex(new rex_sql_index('i_status_timestamp', ['status', 'timestamp']))
            ->ensureIndex(new rex_sql_index('i_description', ['description'], rex_sql_index::FULLTEXT))
            ->ensure();

        static::assertTrue($table->exists());
        static::assertSame(['id', 'title', 'description', 'teaser', 'status', 'timestamp'], array_keys($table->getColumns()));
        static::assertTrue($table->hasIndex('i_status_timestamp'));
        static::assertTrue($table->hasIndex('i_description'));

        rex_sql_table::clearInstance(self::TABLE);
        $table = rex_sql_table::get(self::TABLE);

        $table
            ->ensureColumn(new rex_sql_column('timestamp', 'datetime', true))
            ->ensureColumn(new rex_sql_column('id', 'int(11)', false, null, 'auto_increment'))
            ->ensureColumn(new rex_sql_column('status', 'tinyint(1)'))
            ->ensureColumn(new rex_sql_column('title', 'varchar(20)', false), 'timestamp')
            ->ensureColumn(new rex_sql_column('teaser', 'varchar(255)', false), 'status')
            ->setPrimaryKey(['id', 'title'])
            ->ensureIndex(new rex_sql_index('i_status_timestamp', ['status', 'timestamp'], rex_sql_index::UNIQUE))
            ->ensure();

        $expectedOrder = ['timestamp', 'title', 'id', 'status', 'teaser', 'description'];

        static::assertSame($expectedOrder, array_keys($table->getColumns()));
        static::assertTrue($table->hasIndex('i_status_timestamp'));
        static::assertSame(rex_sql_index::UNIQUE, $table->getIndex('i_status_timestamp')->getType());
        static::assertTrue($table->hasIndex('i_description'));

        rex_sql_table::clearInstance(self::TABLE);
        $table = rex_sql_table::get(self::TABLE);

        static::assertSame(['title', 'id'], $table->getPrimaryKey());
        static::assertTrue($table->hasColumn('description'));
        static::assertNull($table->getColumn('title')->getDefault());
        static::assertSame($expectedOrder, array_keys($table->getColumns()));
        static::assertTrue($table->hasIndex('i_status_timestamp'));
        static::assertSame(rex_sql_index::UNIQUE, $table->getIndex('i_status_timestamp')->getType());
        static::assertTrue($table->hasIndex('i_description'));
    }

    /**
     * @doesNotPerformAssertions
     */
    public function testEnsureMultipleTimes(): void
    {
        for ($i = 0; $i < 3; ++$i) {
            rex_sql_table::get(self::TABLE)
                ->ensurePrimaryIdColumn()
                ->ensureColumn(new rex_sql_column('title', 'varchar(255)'))
                ->ensure();
        }
    }

    public function testEnsureWithEnsureGlobalColumns(): void
    {
        $expectedOrder = ['id', 'title', 'createdate', 'createuser', 'updatedate', 'updateuser', 'revision'];

        for ($i = 1; $i <= 2; ++$i) {
            $table = rex_sql_table::get(self::TABLE);
            $table
                ->ensurePrimaryIdColumn()
                ->ensureColumn(new rex_sql_column('title', 'varchar(255)'))
                ->ensureGlobalColumns()
                ->ensureColumn(new rex_sql_column('revision', 'tinyint(1)'))
                ->ensure();

            rex_sql_table::clearInstance(self::TABLE);
            $table = rex_sql_table::get(self::TABLE);

            static::assertSame($expectedOrder, array_keys($table->getColumns()), "Column order does not match expected order (\$i = $i)");
        }
    }

    public function testRenameNonExistingTable()
    {
        $this->expectException(rex_exception::class);
        $this->expectExceptionMessage('Table "rex_non_existing" does not exist.');

        rex_sql_table::get('rex_non_existing')
            ->setName('rex_foo')
            ->alter();
    }

    public function testClearInstance()
    {
        $table = rex_sql_table::get(self::TABLE);

        rex_sql_table::clearInstance(self::TABLE);
        $table2 = rex_sql_table::get(self::TABLE);

        static::assertNotSame($table2, $table);

        rex_sql_table::clearInstance([1, self::TABLE]);
        $table3 = rex_sql_table::get(self::TABLE);

        static::assertNotSame($table3, $table);
        static::assertNotSame($table3, $table2);
    }
}
