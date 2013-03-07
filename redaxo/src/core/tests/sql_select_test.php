<?php

class rex_sql_select_test extends PHPUnit_Framework_TestCase
{
    const TABLE = rex_sql_test::TABLE;

    private $baseSuite;

    public function setUp()
    {
        parent::setUp();

        $this->baseSuite = new rex_sql_test();
        $this->baseSuite->setUp();

        // Insert a row for later selection tests
        $this->baseSuite->testInsertRow();
    }

    public function tearDown()
    {
        parent::tearDown();

        // Drops the table and all therefore all its rows
        $this->baseSuite->tearDown();
    }

    public function testGetRow()
    {
        // we need some rows for this test
        $this->baseSuite->testInsertRow();
        $this->baseSuite->testInsertRow();

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
}
