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
        $this->assertFalse(rex_sql_table::get(self::TABLE)->exists());

        $this->assertTrue($this->createTable()->exists());

        rex_sql_table::clearInstance(self::TABLE);
        $table = rex_sql_table::get(self::TABLE);

        $this->assertTrue($table->exists());
        $this->assertSame(self::TABLE, $table->getName());

        $this->assertCount(2, $table->getColumns());
        $this->assertTrue($table->hasColumn('id'));
        $this->assertTrue($table->hasColumn('title'));
        $this->assertFalse($table->hasColumn('foo'));
        $this->assertSame(['id'], $table->getPrimaryKey());

        $id = $table->getColumn('id');
        var_dump($id);

        $this->assertSame('id', $id->getName());
        $this->assertSame('int(11)', $id->getType());
        $this->assertFalse($id->isNullable());
        $this->assertNull($id->getDefault());
        $this->assertSame('auto_increment', $id->getExtra());

        $title = $table->getColumn('title');

        $this->assertSame('title', $title->getName());
        $this->assertSame('varchar(255)', $title->getType());
        $this->assertTrue($title->isNullable());
        $this->assertSame('Default title', $title->getDefault());
        $this->assertNull($title->getExtra());

        $this->assertCount(1, $table->getIndexes());
        $this->assertTrue($table->hasIndex('i_title'));
        $this->assertFalse($table->hasIndex('i_foo'));

        $index = $table->getIndex('i_title');

        $this->assertSame('i_title', $index->getName());
        $this->assertSame(rex_sql_index::INDEX, $index->getType());
        $this->assertSame(['title'], $index->getColumns());

        $this->assertTrue($this->createTable2()->exists());

        rex_sql_table::clearInstance(self::TABLE2);
        $table2 = rex_sql_table::get(self::TABLE2);

        $this->assertCount(1, $table2->getForeignKeys());
        $a = $table2->hasForeignKey('test2_fk_test1');
        $this->assertTrue($table2->hasForeignKey('test2_fk_test1'));
        $this->assertFalse($table2->hasForeignKey('foo'));

        $fk = $table2->getForeignKey('test2_fk_test1');

        $this->assertSame('test2_fk_test1', $fk->getName());
        $this->assertSame(self::TABLE, $fk->getTable());
        $this->assertSame(rex_sql_foreign_key::RESTRICT, $fk->getOnUpdate());
        $this->assertSame(rex_sql_foreign_key::RESTRICT, $fk->getOnDelete());
        $this->assertSame(['test1_id' => 'id'], $fk->getColumns());
    }

    public function testDrop()
    {
        $table = $this->createTable();

        $table->drop();

        $this->assertFalse($table->exists());

        rex_sql_table::clearInstance(self::TABLE);
        $table = rex_sql_table::get(self::TABLE);

        $this->assertFalse($table->exists());

        $table->drop();
    }

    public function testSetName()
    {
        $table = $this->createTable();

        $table
            ->setName(self::TABLE2)
            ->alter();

        $this->assertFalse(rex_sql_table::get(self::TABLE)->exists());

        rex_sql_table::clearInstance(self::TABLE2);
        $table = rex_sql_table::get(self::TABLE2);

        $this->assertTrue($table->exists());
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

        $this->assertSame($description, $table->getColumn('description'));

        rex_sql_table::clearInstance(self::TABLE);
        $table = rex_sql_table::get(self::TABLE);

        $this->assertEquals($description, $table->getColumn('description'));

        $this->assertSame(['pid', 'id', 'name', 'title', 'description'], array_keys($table->getColumns()));
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

        $this->assertSame($title, $table->getColumn('title'));
        $this->assertSame($description, $table->getColumn('description'));

        rex_sql_table::clearInstance(self::TABLE);
        $table = rex_sql_table::get(self::TABLE);

        $this->assertEquals($title, $table->getColumn('title'));
        $this->assertEquals($description, $table->getColumn('description'));

        $this->assertSame(['id', 'description', 'title'], array_keys($table->getColumns()));

        $table
            ->ensureColumn($title, 'id')
            ->ensureColumn(new rex_sql_column('status', 'tinyint(1)'), 'id')
            ->ensureColumn(new rex_sql_column('created', 'datetime', false, 'CURRENT_TIMESTAMP'), 'status')
            ->ensureColumn($title, 'status')
            ->alter();

        $expectedOrder = ['id', 'status', 'title', 'created', 'description'];

        $this->assertSame($expectedOrder, array_keys($table->getColumns()));

        rex_sql_table::clearInstance(self::TABLE);
        $table = rex_sql_table::get(self::TABLE);

        $this->assertSame($expectedOrder, array_keys($table->getColumns()));
    }

    public function testEnsurePrimaryIdColumn()
    {
        $table = rex_sql_table::get(self::TABLE);
        $table
            ->ensurePrimaryIdColumn()
            ->create();

        $id = $table->getColumn('id');
        $this->assertSame('int(10) unsigned', $id->getType());
        $this->assertFalse($id->isNullable());
        $this->assertNull($id->getDefault());
        $this->assertSame('auto_increment', $id->getExtra());
        $this->assertSame(['id'], $table->getPrimaryKey());
    }

    public function testEnsureGlobalColumns()
    {
        $table = $this->createTable();
        $table
            ->ensureGlobalColumns()
            ->alter();

        $this->assertTrue($table->hasColumn('createdate'));
        $this->assertSame('datetime', $table->getColumn('createdate')->getType());
        $this->assertTrue($table->hasColumn('createuser'));
        $this->assertSame('varchar(255)', $table->getColumn('createuser')->getType());
        $this->assertTrue($table->hasColumn('updatedate'));
        $this->assertSame('datetime', $table->getColumn('updatedate')->getType());
        $this->assertTrue($table->hasColumn('updateuser'));
        $this->assertSame('varchar(255)', $table->getColumn('updateuser')->getType());
    }

    public function testRenameColumn()
    {
        $table = $this->createTable();

        $table->renameColumn('title', 'name');

        $this->assertFalse($table->hasColumn('title'));
        $this->assertTrue($table->hasColumn('name'));

        $table->alter();

        $this->assertTrue($table->hasColumn('name'));

        rex_sql_table::clearInstance(self::TABLE);
        $table = rex_sql_table::get(self::TABLE);

        $this->assertFalse($table->hasColumn('title'));
        $this->assertTrue($table->hasColumn('name'));
        $this->assertSame('varchar(255)', $table->getColumn('name')->getType());

        $table
            ->renameColumn('id', 'pid')
            ->alter();

        $this->assertSame(['pid'], $table->getPrimaryKey());

        rex_sql_table::clearInstance(self::TABLE);
        $table = rex_sql_table::get(self::TABLE);

        $this->assertSame(['pid'], $table->getPrimaryKey());
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

        $this->assertFalse($table->hasColumn('title'));

        rex_sql_table::clearInstance(self::TABLE);
        $table = rex_sql_table::get(self::TABLE);

        $this->assertFalse($table->hasColumn('title'));
    }

    public function testSetPrimaryKey()
    {
        $table = $this->createTable();

        $primaryKey = ['id', 'title'];
        $table
            ->setPrimaryKey($primaryKey)
            ->alter();

        $this->assertSame($primaryKey, $table->getPrimaryKey());

        rex_sql_table::clearInstance(self::TABLE);
        $table = rex_sql_table::get(self::TABLE);

        $this->assertSame($primaryKey, $table->getPrimaryKey());

        $table->getColumn('id')->setExtra(null);
        $table
            ->setPrimaryKey(null)
            ->alter();

        rex_sql_table::clearInstance(self::TABLE);
        $table = rex_sql_table::get(self::TABLE);

        $this->assertNull($table->getPrimaryKey());

        $table
            ->setPrimaryKey('id')
            ->alter();

        rex_sql_table::clearInstance(self::TABLE);
        $table = rex_sql_table::get(self::TABLE);

        $this->assertSame(['id'], $table->getPrimaryKey());
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

        $this->assertSame($uuid, $table->getIndex('i_uuid'));
        $this->assertSame($description, $table->getIndex('i_description'));
        $this->assertSame($search, $table->getIndex('i_search'));

        rex_sql_table::clearInstance(self::TABLE);
        $table = rex_sql_table::get(self::TABLE);

        $this->assertEquals($uuid, $table->getIndex('i_uuid'));
        $this->assertEquals($description, $table->getIndex('i_description'));
        $this->assertEquals($search, $table->getIndex('i_search'));
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

        $this->assertSame($title, $table->getIndex('i_title'));
        $this->assertSame($title2, $table->getIndex('i_title2'));

        rex_sql_table::clearInstance(self::TABLE);
        $table = rex_sql_table::get(self::TABLE);

        $this->assertEquals($title, $table->getIndex('i_title'));
        $this->assertEquals($title2, $table->getIndex('i_title2'));
    }

    public function testRenameIndex()
    {
        $table = $this->createTable();

        $table->renameIndex('i_title', 'index_title');

        $this->assertFalse($table->hasIndex('i_title'));
        $this->assertTrue($table->hasIndex('index_title'));

        $table->alter();

        $this->assertTrue($table->hasIndex('index_title'));

        rex_sql_table::clearInstance(self::TABLE);
        $table = rex_sql_table::get(self::TABLE);

        $this->assertFalse($table->hasIndex('i_title'));
        $this->assertTrue($table->hasIndex('index_title'));
        $this->assertSame(['title'], $table->getIndex('index_title')->getColumns());
    }

    public function testRemoveIndex()
    {
        $table = $this->createTable();

        $table
            ->removeIndex('i_title')
            ->alter();

        $this->assertFalse($table->hasColumn('i_title'));

        rex_sql_table::clearInstance(self::TABLE);
        $table = rex_sql_table::get(self::TABLE);

        $this->assertFalse($table->hasColumn('i_title'));
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

        $this->assertSame($fk, $table->getForeignKey('test1_fk_config'));

        rex_sql_table::clearInstance(self::TABLE);
        $table = rex_sql_table::get(self::TABLE);

        $this->assertEquals($fk, $table->getForeignKey('test1_fk_config'));
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

        $this->assertSame($fk1, $table2->getForeignKey('test2_fk_test1'));
        $this->assertSame($fk2, $table2->getForeignKey('test2_fk_config'));

        rex_sql_table::clearInstance(self::TABLE2);
        $table2 = rex_sql_table::get(self::TABLE2);

        $this->assertEquals($fk1, $table2->getForeignKey('test2_fk_test1'));
        $this->assertEquals($fk2, $table2->getForeignKey('test2_fk_config'));
    }

    public function testRenameForeignKey()
    {
        $table = $this->createTable();
        $table2 = $this->createTable2();

        $table2->renameForeignKey('test2_fk_test1', 'fk_test2_test1');

        $this->assertFalse($table2->hasForeignKey('test2_fk_test1'));
        $this->assertTrue($table2->hasForeignKey('fk_test2_test1'));

        $table2->alter();

        $this->assertTrue($table2->hasForeignKey('fk_test2_test1'));

        rex_sql_table::clearInstance(self::TABLE2);
        $table2 = rex_sql_table::get(self::TABLE2);

        $this->assertFalse($table2->hasForeignKey('test2_fk_test1'));
        $this->assertTrue($table2->hasForeignKey('fk_test2_test1'));
        $this->assertSame(['test1_id' => 'id'], $table2->getForeignKey('fk_test2_test1')->getColumns());
    }

    public function testRemoveForeignKey()
    {
        $table = $this->createTable();
        $table2 = $this->createTable2();

        $table2
            ->removeForeignKey('test2_fk_test1')
            ->alter();

        $this->assertFalse($table2->hasForeignKey('test2_fk_test1'));

        rex_sql_table::clearInstance(self::TABLE2);
        $table2 = rex_sql_table::get(self::TABLE2);

        $this->assertFalse($table2->hasForeignKey('test2_fk_test1'));
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

        $this->assertFalse($table->hasColumn('title'));
        $this->assertFalse($table->hasIndex('i_title'));
        $this->assertTrue($table->hasColumn('name'));
        $this->assertTrue($table->hasIndex('i_name'));
        $this->assertSame('int(10) unsigned', $table->getColumn('id')->getType());
        $this->assertEquals(['id', 'name'], $table->getPrimaryKey());
        $this->assertEquals(['name'], $table->getIndex('i_name')->getColumns());
    }

    public function testEnsure()
    {
        $table = rex_sql_table::get(self::TABLE);
        $table
            ->ensureColumn(new rex_sql_column('title', 'varchar(255)', false, 'Default title'))
            ->ensureColumn(new rex_sql_column('id', 'int(11)', false, null, 'auto_increment'), rex_sql_table::FIRST)
            ->ensureColumn(new rex_sql_column('status', 'tinyint(1)'))
            ->ensureColumn(new rex_sql_column('timestamp', 'datetime', true))
            ->ensureColumn(new rex_sql_column('description', 'text', true), 'title')
            ->setPrimaryKey('id')
            ->ensureIndex(new rex_sql_index('i_status_timestamp', ['status', 'timestamp']))
            ->ensureIndex(new rex_sql_index('i_description', ['description'], rex_sql_index::FULLTEXT))
            ->ensure();

        $this->assertTrue($table->exists());
        $this->assertSame(['id', 'title', 'description', 'status', 'timestamp'], array_keys($table->getColumns()));
        $this->assertTrue($table->hasIndex('i_status_timestamp'));
        $this->assertTrue($table->hasIndex('i_description'));

        rex_sql_table::clearInstance(self::TABLE);
        $table = rex_sql_table::get(self::TABLE);

        $table
            ->ensureColumn(new rex_sql_column('timestamp', 'datetime', true))
            ->ensureColumn(new rex_sql_column('id', 'int(11)', false, null, 'auto_increment'))
            ->ensureColumn(new rex_sql_column('status', 'tinyint(1)'))
            ->ensureColumn(new rex_sql_column('title', 'varchar(20)', false), 'timestamp')
            ->setPrimaryKey(['id', 'title'])
            ->ensureIndex(new rex_sql_index('i_status_timestamp', ['status', 'timestamp'], rex_sql_index::UNIQUE))
            ->ensure();

        $expectedOrder = ['timestamp', 'title', 'id', 'status', 'description'];

        $this->assertSame($expectedOrder, array_keys($table->getColumns()));
        $this->assertTrue($table->hasIndex('i_status_timestamp'));
        $this->assertSame(rex_sql_index::UNIQUE, $table->getIndex('i_status_timestamp')->getType());
        $this->assertTrue($table->hasIndex('i_description'));

        rex_sql_table::clearInstance(self::TABLE);
        $table = rex_sql_table::get(self::TABLE);

        $this->assertSame(['title', 'id'], $table->getPrimaryKey());
        $this->assertTrue($table->hasColumn('description'));
        $this->assertNull($table->getColumn('title')->getDefault());
        $this->assertSame($expectedOrder, array_keys($table->getColumns()));
        $this->assertTrue($table->hasIndex('i_status_timestamp'));
        $this->assertSame(rex_sql_index::UNIQUE, $table->getIndex('i_status_timestamp')->getType());
        $this->assertTrue($table->hasIndex('i_description'));
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

        $this->assertNotSame($table2, $table);

        rex_sql_table::clearInstance([1, self::TABLE]);
        $table3 = rex_sql_table::get(self::TABLE);

        $this->assertNotSame($table3, $table);
        $this->assertNotSame($table3, $table2);
    }
}
