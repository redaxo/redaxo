<?php

class rex_sql_select_test extends PHPUnit_Framework_TestCase
{
    const TABLE = 'rex_tests';

    public function setUp()
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

    public function tearDown()
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

        $this->assertEquals(6, count($sql->getRow()), 'getRow() returns an array containing all columns of the ResultSet');
        $this->assertEquals(3, $sql->getRows(), 'getRows() returns the number of rows');

        foreach ($sql as $row) {
            $this->assertTrue($row->hasValue('col_str'), 'values exist in each row');
            $this->assertTrue($row->hasValue('col_int'), 'values exist in each row');

            $this->assertEquals('abc', $row->getValue('col_str'), 'get a string');
            $this->assertEquals(5, $row->getValue('col_int'), 'get an int ');

            $this->assertEquals('abc', $row->getValue(self::TABLE . '.col_str'), 'get a string with table.col notation');
            $this->assertEquals(5, $row->getValue(self::TABLE . '.col_int'), 'get an int with table.col notation');
        }
    }

    public function testGetVariations()
    {
        $sql = rex_sql::factory();
        $sql->setQuery('SELECT * FROM ' . self::TABLE . ' WHERE col_int = 5');

        $this->assertEquals(1, $sql->getRows());

        $this->assertTrue($sql->hasValue('col_str'), 'hasValue() checks field by name');
        $this->assertTrue($sql->hasValue(self::TABLE . '.col_str'), 'hasValue() checks field by table.fieldname');

        $this->assertEquals('abc', $sql->getValue('col_str'), 'getValue() retrievs field by name');
        $this->assertEquals('abc', $sql->getValue(self::TABLE . '.col_str'), 'getValue() retrievs field by table.fieldname');
    }

    public function testGetArray()
    {
        $sql = rex_sql::factory();
        $array = $sql->getArray('SELECT * FROM ' . self::TABLE . ' WHERE col_int = 5');

        $this->assertEquals(1, $sql->getRows(), 'getRows() returns the number of rows');
        $this->assertEquals(1, count($array), 'the returned array contain the correct number of rows');
        $this->assertArrayHasKey(0, $array);

        $row1 = $array[0];
        $this->assertEquals('abc', $row1['col_str']);
        $this->assertEquals('5', $row1['col_int']);
    }

    public function testGetDbArray()
    {
        $sql = rex_sql::factory();
        $array = $sql->getDBArray('(DB1) SELECT * FROM ' . self::TABLE . ' WHERE col_int = 5');

        $this->assertEquals(1, $sql->getRows(), 'getRows() returns the number of rows');
        $this->assertEquals(1, count($array), 'the returned array contain the correct number of rows');
        $this->assertArrayHasKey(0, $array);

        $row1 = $array[0];
        $this->assertEquals('abc', $row1['col_str']);
        $this->assertEquals('5', $row1['col_int']);
    }

    public function testPreparedSetQuery()
    {
        $sql = rex_sql::factory();
        $sql->setQuery('SELECT * FROM ' . self::TABLE . ' WHERE col_str = ? and col_int = ?', ['abc', 5]);

        $this->assertEquals(1, $sql->getRows());
    }

    public function testPreparedNamedSetQuery()
    {
        $sql = rex_sql::factory();
        $sql->setQuery('SELECT * FROM ' . self::TABLE . ' WHERE col_str = :mystr and col_int = :myint', ['mystr' => 'abc', ':myint' => 5]);

        $this->assertEquals(1, $sql->getRows());
    }

    public function testPreparedSetQueryWithReset()
    {
        $sql = rex_sql::factory();
        $sql->setQuery('SELECT * FROM ' . self::TABLE . ' WHERE col_str = ? and col_int = ?', ['abc', 5]);

        $sql->reset();

        $this->assertEquals(1, $sql->getRows());
    }

    public function testGetArrayAfterPreparedSetQuery()
    {
        $sql = rex_sql::factory();
        $sql->setQuery('SELECT * FROM ' . self::TABLE . ' WHERE col_int = ?', [5]);
        $array = $sql->getArray();

        $this->assertEquals(1, $sql->getRows());
        $this->assertArrayHasKey(0, $array);

        $row1 = $array[0];
        $this->assertEquals('abc', $row1['col_str']);
        $this->assertEquals('5', $row1['col_int']);
    }

    public function testGetArrayAfterSetQuery()
    {
        $sql = rex_sql::factory();
        $sql->setQuery('SELECT * FROM ' . self::TABLE . ' WHERE col_int = 5');
        $array = $sql->getArray();

        $this->assertEquals(1, $sql->getRows());
        $this->assertArrayHasKey(0, $array);

        $row1 = $array[0];
        $this->assertEquals('abc', $row1['col_str']);
        $this->assertEquals('5', $row1['col_int']);
    }

    public function testArrayFetchTypeNum()
    {
        $sql = rex_sql::factory();
        $array = $sql->getArray('SELECT * FROM ' . self::TABLE . ' WHERE col_int = 5', [], PDO::FETCH_NUM);

        $row1 = $array[0];
        $this->assertEquals('abc', $row1[1]);
        $this->assertEquals('5', $row1[2]);
        $this->assertEquals('mytext', $row1[5]);
        $this->assertEquals('mytext', $row1[5]);
    }

    public function testDBArrayFetchTypeNum()
    {
        $sql = rex_sql::factory();
        $array = $sql->getDBArray('SELECT * FROM ' . self::TABLE . ' WHERE col_int = 5', [], PDO::FETCH_NUM);

        $row1 = $array[0];
        $this->assertEquals('abc', $row1[1]);
        $this->assertEquals('5', $row1[2]);
        $this->assertEquals('mytext', $row1[5]);
        $this->assertEquals('mytext', $row1[5]);
    }

    public function testError()
    {
        $sql = rex_sql::factory();

        $sql->setQuery('SELECT * FROM '.self::TABLE);

        $this->assertFalse($sql->hasError());
        $this->assertEquals(0, $sql->getErrno());

        $exception = null;
        try {
            $sql->setQuery('SELECT '.self::TABLE);
        } catch (rex_sql_exception $exception) {
        }

        $this->assertInstanceOf(rex_sql_exception::class, $exception);
        $this->assertTrue($sql->hasError());
        $this->assertEquals('42S22', $sql->getErrno());
        $this->assertEquals(1054, $sql->getMysqlErrno());
        $this->assertEquals("Unknown column 'rex_tests' in 'field list'", $sql->getError());

        $exception = null;
        try {
            $sql->setQuery('SELECT * FROM '.self::TABLE.' WHERE idx = ?', [1]);
        } catch (rex_sql_exception $exception) {
        }

        $this->assertInstanceOf(rex_sql_exception::class, $exception);
        $this->assertTrue($sql->hasError());
        $this->assertEquals('42S22', $sql->getErrno());
        $this->assertEquals(1054, $sql->getMysqlErrno());
        $this->assertEquals("Unknown column 'idx' in 'where clause'", $sql->getError());
    }

    public function testUnbufferedQuery()
    {
        $sql = rex_sql::factory();

        // get DB 1 PDO object
        $property = new ReflectionProperty(rex_sql::class, 'pdo');
        $property->setAccessible(true);
        /** @var PDO $pdo */
        $pdo = $property->getValue()[1];

        $this->assertEquals(1, $pdo->getAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY));

        $sql->setQuery('SELECT * FROM '.self::TABLE, [], [
            rex_sql::OPT_BUFFERED => false,
        ]);

        $this->assertEquals(1, $pdo->getAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY));

        try {
            $sql->setQuery('SELECT '.self::TABLE, [], [
                rex_sql::OPT_BUFFERED => false,
            ]);
        } catch (rex_sql_exception $e) {
        }

        $this->assertEquals(1, $pdo->getAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY));
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
