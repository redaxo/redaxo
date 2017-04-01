<?php

class rex_sql_table_test extends PHPUnit_Framework_TestCase
{
    const TABLE = 'rex_sql_table_test';
    const TABLE2 = 'rex_sql_table_test2';

    protected function tearDown()
    {
        $sql = rex_sql::factory();
        $sql->setQuery('DROP TABLE IF EXISTS `' . self::TABLE . '`');
        $sql->setQuery('DROP TABLE IF EXISTS `' . self::TABLE2 . '`');

        rex_sql_table::clearInstancePool();
    }

    protected function createTable()
    {
        $table = rex_sql_table::get(self::TABLE);

        $table
            ->addColumn(new rex_sql_column('id', 'int(11)', false, null, 'auto_increment'))
            ->addColumn(new rex_sql_column('title', 'varchar(255)', true, 'Default title'))
            ->setPrimaryKey('id')
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
            ->ensureColumn(new rex_sql_column('created', 'datetime'), 'status')
            ->ensureColumn($title, 'status')
            ->alter();

        $expectedOrder = ['id', 'status', 'title', 'created', 'description'];

        $this->assertSame($expectedOrder, array_keys($table->getColumns()));

        rex_sql_table::clearInstance(self::TABLE);
        $table = rex_sql_table::get(self::TABLE);

        $this->assertSame($expectedOrder, array_keys($table->getColumns()));
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

    /**
     * @expectedException \rex_exception
     */
    public function testRenameColumnNonExisting()
    {
        $table = $this->createTable();
        $table->renameColumn('foo', 'bar');
    }

    /**
     * @expectedException \rex_exception
     */
    public function testRenameColumnToAlreadyExisting()
    {
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
            ->alter();

        rex_sql_table::clearInstance(self::TABLE2);
        $table = rex_sql_table::get(self::TABLE2);

        $this->assertFalse($table->hasColumn('title'));
        $this->assertTrue($table->hasColumn('name'));
        $this->assertSame('int(10) unsigned', $table->getColumn('id')->getType());
        $this->assertEquals(['id', 'name'], $table->getPrimaryKey());
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
            ->ensure();

        $this->assertTrue($table->exists());
        $this->assertSame(['id', 'title', 'description', 'status', 'timestamp'], array_keys($table->getColumns()));

        rex_sql_table::clearInstance(self::TABLE);
        $table = rex_sql_table::get(self::TABLE);

        $table
            ->ensureColumn(new rex_sql_column('timestamp', 'datetime', true))
            ->ensureColumn(new rex_sql_column('id', 'int(11)', false, null, 'auto_increment'))
            ->ensureColumn(new rex_sql_column('status', 'tinyint(1)'))
            ->ensureColumn(new rex_sql_column('title', 'varchar(20)', false), 'timestamp')
            ->setPrimaryKey(['id', 'title'])
            ->ensure();

        $expectedOrder = ['timestamp', 'title', 'id', 'status', 'description'];

        $this->assertSame($expectedOrder, array_keys($table->getColumns()));

        rex_sql_table::clearInstance(self::TABLE);
        $table = rex_sql_table::get(self::TABLE);

        $this->assertSame(['title', 'id'], $table->getPrimaryKey());
        $this->assertTrue($table->hasColumn('description'));
        $this->assertNull($table->getColumn('title')->getDefault());
        $this->assertSame($expectedOrder, array_keys($table->getColumns()));
    }
}
