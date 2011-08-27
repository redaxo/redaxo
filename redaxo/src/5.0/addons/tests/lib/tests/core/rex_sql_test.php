<?php
class rex_sql_test extends PHPUnit_TestCase
{
  const TABLE = 'rex_tests';
  
  public function setUp()
  {
    $sql = rex_sql::factory();
    
    $sql->setQuery('DROP TABLE IF EXISTS `'. self::TABLE .'`');
    $sql->setQuery('CREATE TABLE `'. self::TABLE .'` (
        `id` INT NOT NULL AUTO_INCREMENT ,
        `str` VARCHAR( 255 ) NOT NULL ,
        `int` INT NOT NULL ,
        `date` DATE NOT NULL ,
        `time` DATETIME NOT NULL ,
        `text` TEXT NOT NULL ,
        PRIMARY KEY ( `id` )
        ) ENGINE = MYISAM ;');
  }
  
  public function tearDown()
  {
    $sql = rex_sql::factory();
    $sql->setQuery('DROP TABLE `'. self::TABLE .'`');
  }
  
  public function testInsertRow()
  {
    $sql = rex_sql::factory();
    $sql->setTable(self::TABLE);
    $sql->setValue('str', 'abc');
    $sql->setValue('int', 5);
    
    $sql->insert();
    $this->assertEquals(1, $sql->getRows());
  }
}