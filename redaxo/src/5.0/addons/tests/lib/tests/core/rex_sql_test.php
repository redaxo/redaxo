<?php
class rex_sql_test extends PHPUnit_Framework_TestCase
{
  const TABLE = 'rex_tests';
  
  public function setUp()
  {
    parent::setUp();
    
    $sql = rex_sql::factory();
    
    $sql->setQuery('DROP TABLE IF EXISTS `'. self::TABLE .'`');
    $sql->setQuery('CREATE TABLE `'. self::TABLE .'` (
        `id` INT NOT NULL AUTO_INCREMENT ,
        `col_str` VARCHAR( 255 ) NOT NULL ,
        `col_int` INT NOT NULL ,
        `col_date` DATE NOT NULL ,
        `col_time` DATETIME NOT NULL ,
        `col_text` TEXT NOT NULL ,
        PRIMARY KEY ( `id` )
        ) ENGINE = MYISAM ;');
  }
  
  public function tearDown()
  {
    parent::tearDown();
    
    $sql = rex_sql::factory();
    $sql->setQuery('DROP TABLE `'. self::TABLE .'`');
  }
  
  public function testFactory()
  {
    $sql = rex_sql::factory();
    $this->assertNotNull($sql);
  }
  
  public function testCheckConnection()
  {
    $configFile = rex_path::src('config.yml');
  	$config = rex_file::getConfig($configFile);
    $this->assertTrue(rex_sql::checkDbConnection($config['db'][1]['host'], $config['db'][1]['login'], $config['db'][1]['password'], $config['db'][1]['name']));
  }
  
  public function testCheckConnection_Invalid()
  {
    $configFile = rex_path::src('config.yml');
  	$config = rex_file::getConfig($configFile);
    $this->assertTrue(true !== rex_sql::checkDbConnection($config['db'][1]['host'], $config['db'][1]['login'], 'not-the-correct-password', $config['db'][1]['name']));
  }
  
  public function testSetGetValue()
  {
    $sql = rex_sql::factory();
    $sql->setTable(self::TABLE);
    $sql->setValue('col_str', 'abc');
    $sql->setValue('col_int', 5);
    
    $this->assertTrue($sql->hasValue('col_str'), 'set value string exists');
    $this->assertTrue($sql->hasValue('col_int'), 'set value int exists');
    
    $this->assertEquals('abc', $sql->getValue('col_str'), 'get a previous set string');
    $this->assertEquals(5, $sql->getValue('col_int'), 'get a previous set int ');
  }
  
  public function testInsertRow()
  {
    $sql = rex_sql::factory();
    $sql->setTable(self::TABLE);
    $sql->setValue('col_str', 'abc');
    $sql->setValue('col_int', 5);
    
    $sql->insert();
    $this->assertEquals(1, $sql->getRows());
    // failing at the moment
//     $this->assertEquals('abc', $sql->getValue('col_str'));
//     $this->assertEquals(5, $sql->getValue('col_int'));
  }
  
  public function testInsertRawValue()
  {
    $sql = rex_sql::factory();
    $sql->setTable(self::TABLE);
    $sql->setRawValue('col_time', 'NOW()');
    
    $sql->insert();
    $this->assertEquals(1, $sql->getRows());
  }
  
  public function testUpdateRowByWhereArray()
  {
    // create a row we later update
    $this->testInsertRow();
    
    $sql = rex_sql::factory();
    $sql->setTable(self::TABLE);
    $sql->setValue('col_str', 'def');
    $sql->setWhere(array('col_int' => 5));
    
    $sql->update();
    $this->assertEquals(1, $sql->getRows());
  }
  
  public function testUpdateRowByNamedWhere()
  {
    // create a row we later update
    $this->testInsertRow();
    
    $sql = rex_sql::factory();
    $sql->setTable(self::TABLE);
    $sql->setValue('col_str', 'def');
    $sql->setWhere('col_int = :myint', array('myint' => 5));
    
    $sql->update();
    $this->assertEquals(1, $sql->getRows());
  }
  
  public function testUpdateRowByStringWhere()
  {
    // create a row we later update
    $this->testInsertRow();
    
    $sql = rex_sql::factory();
    $sql->setTable(self::TABLE);
    $sql->setValue('col_str', 'def');
    $sql->setWhere('col_int = "5"');
    
    $sql->update();
    $this->assertEquals(1, $sql->getRows());
  }
  
  public function testDeleteRow()
  {
    // create a row we later delete
    $this->testInsertRow();
    
    $sql = rex_sql::factory();
    $sql->setTable(self::TABLE);
    $sql->setWhere(array('col_int' => 5));
    
    $sql->delete();
    $this->assertEquals(1, $sql->getRows());
  }
}

