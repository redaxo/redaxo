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
  
  public function testGetArray()
  {
    $sql = rex_sql::factory();
    $array = $sql->getArray('SELECT * FROM '. self::TABLE .' WHERE col_int = 5');

    $this->assertEquals(1, $sql->getRows());
    $this->assertArrayHasKey(0, $array);
    
    $row1 = $array[0];
    $this->assertEquals('abc', $row1['col_str']);
    $this->assertEquals('5', $row1['col_int']);
  }
  
  public function testGetDbArray()
  {
    $sql = rex_sql::factory();
    $array = $sql->getDBArray('(DB1) SELECT * FROM '. self::TABLE .' WHERE col_int = 5');

    $this->assertEquals(1, $sql->getRows());
    $this->assertArrayHasKey(0, $array);
    
    $row1 = $array[0];
    $this->assertEquals('abc', $row1['col_str']);
    $this->assertEquals('5', $row1['col_int']);
  }
  
  public function testGetArrayAfterPreparedSetQuery()
  {
    $sql = rex_sql::factory();
    $sql->setQuery('SELECT * FROM '. self::TABLE .' WHERE col_int = ?', array(5));
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
    $sql->setQuery('SELECT * FROM '. self::TABLE .' WHERE col_int = 5');
    $array = $sql->getArray();

    $this->assertEquals(1, $sql->getRows());
    $this->assertArrayHasKey(0, $array);
    
    $row1 = $array[0];
    $this->assertEquals('abc', $row1['col_str']);
    $this->assertEquals('5', $row1['col_int']);
  }

  public function testPreparedSetQuery()
  {
    $sql = rex_sql::factory();
    $sql->setQuery('SELECT * FROM '. self::TABLE .' WHERE col_str = ? and col_int = ?', array('abc', 5));
  
    $this->assertEquals(1, $sql->getRows());
  }
  
  public function testPreparedNamedSetQuery()
  {
    $sql = rex_sql::factory();
    $sql->setQuery('SELECT * FROM '. self::TABLE .' WHERE col_str = :mystr and col_int = :myint', array('mystr' => 'abc', ':myint' => 5));
  
    $this->assertEquals(1, $sql->getRows());
  }
}