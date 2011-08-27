<?php 

class rex_sql_select_test extends PHPUnit_TestCase
{
  const TABLE = rex_sql_test::TABLE;
  
  private $baseSuite;
  
  public function setUp()
  {
    parent::setUp();
    
    $this->baseSuite = new rex_sql_test();
    $this->baseSuite->setUp();
    
    $this->baseSuite->testInsertRow();
  }
  
  public function tearDown()
  {
    parent::tearDown();
    
    $this->baseSuite->tearDown();
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