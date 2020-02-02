<?php

use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
class rex_sql_select_test extends TestCase
{
    public const TABLE = 'rex_tests';

    protected function setUp()
    {
        parent::setUp();

        $sql = rex_sql::factory();

        $sql->setQuery('DROP TABLE IF EXISTS `' . self::TABLE . '`');
        $sql->setQuery('CREATE TABLE `' . self::TABLE . '` (
            `id` INT NOT NULL AUTO_INCREMENT ,
            `col_str` VARCHAR( 255 ) NOT NULL ,
            `col_int` INT NOT NULL ,
            `col_date` DATE NOT NULL ,
            `col_time` DATETIME NOT NULL ,
            `col_text` TEXT NOT NULL ,
            PRIMARY KEY ( `id` )
            ) ENGINE = InnoDB ;');

        // Insert a row for later selection tests
        $this->insertRow();
    }

    protected function tearDown()
    {
        parent::tearDown();

        // Drops the table and all therefore all its rows
        $sql = rex_sql::factory();
        $sql->setQuery('DROP TABLE `' . self::TABLE . '`');
    }

    public function testGetRow()
    {
        // we need some rows for this test
        $this->insertRow();
        $this->insertRow();

        $sql = rex_sql::factory();
        $sql->setQuery('SELECT * FROM ' . self::TABLE . ' WHERE col_int = ?', [5]);

        static::assertCount(6, $sql->getRow(), 'getRow() returns an array containing all columns of the ResultSet');
        static::assertEquals(3, $sql->getRows(), 'getRows() returns the number of rows');

        foreach ($sql as $row) {
            static::assertTrue($row->hasValue('col_str'), 'values exist in each row');
            static::assertTrue($row->hasValue('col_int'), 'values exist in each row');

            static::assertEquals('abc', $row->getValue('col_str'), 'get a string');
            static::assertEquals(5, $row->getValue('col_int'), 'get an int ');

            static::assertEquals('abc', $row->getValue(self::TABLE . '.col_str'), 'get a string with table.col notation');
            static::assertEquals(5, $row->getValue(self::TABLE . '.col_int'), 'get an int with table.col notation');
        }
    }

    public function testGetRowAsObject()
    {
        $this->insertRow();
        $this->insertRow();

        $sql = rex_sql::factory();
        $sql->setQuery('SELECT * FROM ' . self::TABLE . ' ORDER BY id');

        $row = $sql->getRow(PDO::FETCH_OBJ);

        static::assertInstanceOf(stdClass::class, $row);
        static::assertEquals(1, $row->{self::TABLE.'.id'});

        $sql->next();
        $row = $sql->getRow(PDO::FETCH_OBJ);

        static::assertInstanceOf(stdClass::class, $row);
        static::assertEquals(2, $row->{self::TABLE.'.id'});
    }

    public function testGetVariations()
    {
        $sql = rex_sql::factory();
        $sql->setQuery('SELECT * FROM ' . self::TABLE . ' WHERE col_int = 5');

        static::assertEquals(1, $sql->getRows());

        static::assertTrue($sql->hasValue('col_str'), 'hasValue() checks field by name');
        static::assertTrue($sql->hasValue(self::TABLE . '.col_str'), 'hasValue() checks field by table.fieldname');

        static::assertEquals('abc', $sql->getValue('col_str'), 'getValue() retrievs field by name');
        static::assertEquals('abc', $sql->getValue(self::TABLE . '.col_str'), 'getValue() retrievs field by table.fieldname');
    }

    public function testGetArray()
    {
        $sql = rex_sql::factory();
        $array = $sql->getArray('SELECT * FROM ' . self::TABLE . ' WHERE col_int = 5');

        static::assertEquals(1, $sql->getRows(), 'getRows() returns the number of rows');
        static::assertCount(1, $array, 'the returned array contain the correct number of rows');
        static::assertArrayHasKey(0, $array);

        $row1 = $array[0];
        static::assertEquals('abc', $row1['col_str']);
        static::assertEquals('5', $row1['col_int']);
    }

    public function testGetDbArray()
    {
        $sql = rex_sql::factory();
        $array = $sql->getDBArray('(DB1) SELECT * FROM ' . self::TABLE . ' WHERE col_int = 5');

        static::assertEquals(1, $sql->getRows(), 'getRows() returns the number of rows');
        static::assertCount(1, $array, 'the returned array contain the correct number of rows');
        static::assertArrayHasKey(0, $array);

        $row1 = $array[0];
        static::assertEquals('abc', $row1['col_str']);
        static::assertEquals('5', $row1['col_int']);
    }

    public function testPreparedSetQuery()
    {
        $sql = rex_sql::factory();
        $sql->setQuery('SELECT * FROM ' . self::TABLE . ' WHERE col_str = ? and col_int = ?', ['abc', 5]);

        static::assertEquals(1, $sql->getRows());
    }

    public function testPreparedNamedSetQuery()
    {
        $sql = rex_sql::factory();
        $sql->setQuery('SELECT * FROM ' . self::TABLE . ' WHERE col_str = :mystr and col_int = :myint', ['mystr' => 'abc', ':myint' => 5]);

        static::assertEquals(1, $sql->getRows());
    }

    public function testPreparedSetQueryWithReset()
    {
        $sql = rex_sql::factory();
        $sql->setQuery('SELECT * FROM ' . self::TABLE . ' WHERE col_str = ? and col_int = ?', ['abc', 5]);

        $sql->reset();

        static::assertEquals(1, $sql->getRows());
    }

    public function testGetArrayAfterPreparedSetQuery()
    {
        $sql = rex_sql::factory();
        $sql->setQuery('SELECT * FROM ' . self::TABLE . ' WHERE col_int = ?', [5]);
        $array = $sql->getArray();

        static::assertEquals(1, $sql->getRows());
        static::assertArrayHasKey(0, $array);

        $row1 = $array[0];
        static::assertEquals('abc', $row1['col_str']);
        static::assertEquals('5', $row1['col_int']);
    }

    public function testGetArrayAfterSetQuery()
    {
        $sql = rex_sql::factory();
        $sql->setQuery('SELECT * FROM ' . self::TABLE . ' WHERE col_int = 5');
        $array = $sql->getArray();

        static::assertEquals(1, $sql->getRows());
        static::assertArrayHasKey(0, $array);

        $row1 = $array[0];
        static::assertEquals('abc', $row1['col_str']);
        static::assertEquals('5', $row1['col_int']);
    }

    public function testArrayFetchTypeNum()
    {
        $sql = rex_sql::factory();
        $array = $sql->getArray('SELECT * FROM ' . self::TABLE . ' WHERE col_int = 5', [], PDO::FETCH_NUM);

        $row1 = $array[0];
        static::assertEquals('abc', $row1[1]);
        static::assertEquals('5', $row1[2]);
        static::assertEquals('mytext', $row1[5]);
        static::assertEquals('mytext', $row1[5]);
    }

    public function testDBArrayFetchTypeNum()
    {
        $sql = rex_sql::factory();
        $array = $sql->getDBArray('SELECT * FROM ' . self::TABLE . ' WHERE col_int = 5', [], PDO::FETCH_NUM);

        $row1 = $array[0];
        static::assertEquals('abc', $row1[1]);
        static::assertEquals('5', $row1[2]);
        static::assertEquals('mytext', $row1[5]);
        static::assertEquals('mytext', $row1[5]);
    }

    public function testHasNext()
    {
        $sql = rex_sql::factory();
        $sql->setQuery('SELECT * FROM ' . self::TABLE);

        static::assertTrue($sql->hasNext());

        $sql->next();
        static::assertFalse($sql->hasNext());

        $sql->next();
        static::assertFalse($sql->hasNext());
    }

    public function testError()
    {
        $sql = rex_sql::factory();

        $sql->setQuery('SELECT * FROM '.self::TABLE);

        static::assertFalse($sql->hasError());
        static::assertEquals(0, $sql->getErrno());

        $exception = null;
        try {
            $sql->setQuery('SELECT '.self::TABLE);
        } catch (rex_sql_exception $exception) {
        }

        static::assertInstanceOf(rex_sql_exception::class, $exception);
        static::assertTrue($sql->hasError());
        static::assertEquals('42S22', $sql->getErrno());
        static::assertEquals(1054, $sql->getMysqlErrno());
        static::assertEquals("Unknown column 'rex_tests' in 'field list'", $sql->getError());

        $exception = null;
        try {
            $sql->setQuery('SELECT * FROM '.self::TABLE.' WHERE idx = ?', [1]);
        } catch (rex_sql_exception $exception) {
        }

        static::assertInstanceOf(rex_sql_exception::class, $exception);
        static::assertTrue($sql->hasError());
        static::assertEquals('42S22', $sql->getErrno());
        static::assertEquals(1054, $sql->getMysqlErrno());
        static::assertEquals("Unknown column 'idx' in 'where clause'", $sql->getError());
    }

    public function testUnbufferedQuery()
    {
        $sql = rex_sql::factory();

        // get DB 1 PDO object
        $property = new ReflectionProperty(rex_sql::class, 'pdo');
        $property->setAccessible(true);
        /** @var PDO $pdo */
        $pdo = $property->getValue()[1];

        static::assertEquals(1, $pdo->getAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY));

        $sql->setQuery('SELECT * FROM '.self::TABLE, [], [
            rex_sql::OPT_BUFFERED => false,
        ]);

        static::assertEquals(1, $pdo->getAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY));

        try {
            $sql->setQuery('SELECT '.self::TABLE, [], [
                rex_sql::OPT_BUFFERED => false,
            ]);
        } catch (rex_sql_exception $e) {
        }

        static::assertEquals(1, $pdo->getAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY));
    }

    private function insertRow()
    {
        $sql = rex_sql::factory();
        $sql->setTable(self::TABLE);
        $sql->setValue('col_int', 5);
        $sql->setValue('col_str', 'abc');
        $sql->setValue('col_text', 'mytext');

        $sql->insert();
    }
}
