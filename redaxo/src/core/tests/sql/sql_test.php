<?php

use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
class rex_sql_test extends TestCase
{
    public const TABLE = 'rex_tests_table';
    public const VIEW = 'rex_tests_view';

    protected function setUp(): void
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

        $sql->setQuery('DROP VIEW IF EXISTS `' . self::VIEW . '`');
        $sql->setQuery('CREATE VIEW `' . self::VIEW . '` AS SELECT * FROM `'.self::TABLE.'`');
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $sql = rex_sql::factory();
        $sql->setQuery('DROP TABLE `' . self::TABLE . '`');
        $sql->setQuery('DROP VIEW `' . self::VIEW . '`');
    }

    public function testFactory()
    {
        $sql = rex_sql::factory();
        static::assertNotNull($sql);
    }

    public function testCheckConnection()
    {
        $dbConfig = rex::getDbConfig();
        static::assertTrue(rex_sql::checkDbConnection($dbConfig->host, $dbConfig->login, $dbConfig->password, $dbConfig->name));
    }

    public function testCheckConnectionInvalidPassword()
    {
        $dbConfig = rex::getDbConfig();
        static::assertTrue(true !== rex_sql::checkDbConnection($dbConfig->host, $dbConfig->login, 'fu-password', $dbConfig->name));
    }

    public function testCheckConnectionInvalidHost()
    {
        $dbConfig = rex::getDbConfig();
        static::assertTrue(true !== rex_sql::checkDbConnection('fu-host', $dbConfig->login, $dbConfig->password, $dbConfig->name));
    }

    public function testCheckConnectionInvalidLogin()
    {
        $dbConfig = rex::getDbConfig();
        static::assertTrue(true !== rex_sql::checkDbConnection($dbConfig->host, 'fu-login', $dbConfig->password, $dbConfig->name));
    }

    public function testCheckConnectionInvalidDatabase()
    {
        $dbConfig = rex::getDbConfig();
        static::assertTrue(true !== rex_sql::checkDbConnection($dbConfig->host, $dbConfig->login, $dbConfig->password, 'fu-database'));
    }

    /**
     * @dataProvider provideDbType
     */
    public function testDbType(string $expected, string $version): void
    {
        $sql = $this->getVersionMock($version);

        static::assertSame($expected, $sql->getDbType());
    }

    public function provideDbType(): array
    {
        return [
            [rex_sql::MYSQL, '5.7.7'],
            [rex_sql::MYSQL, '5.6.19-67.0-log'],
            [rex_sql::MARIADB, '10.2.0-mariadb'],
            [rex_sql::MARIADB, '5.5.5-10.4.10-MariaDB'],
            [rex_sql::MARIADB, '5.5.5-10.3.18-MariaDB-log'],
        ];
    }

    /**
     * @dataProvider provideDbVersion
     */
    public function testDbVersion(string $expected, string $version): void
    {
        $sql = $this->getVersionMock($version);

        static::assertSame($expected, $sql->getDbVersion());
    }

    public function provideDbVersion(): array
    {
        return [
            ['5.7.7', '5.7.7'],
            ['5.6.19', '5.6.19-67.0-log'],
            ['10.2.0', '10.2.0-mariadb'],
            ['10.4.10', '5.5.5-10.4.10-MariaDB'],
            ['10.3.18', '5.5.5-10.3.18-MariaDB-log'],
            ['unexpected-1.0-foo', 'unexpected-1.0-foo'],
        ];
    }

    private function getVersionMock(string $version): rex_sql
    {
        return new class($version) extends rex_sql {
            public function __construct(string $version)
            {
                $this->DBID = 999;

                /** @psalm-suppress InvalidPropertyAssignmentValue */
                self::$pdo[$this->DBID] = new class($version) {
                    private $version;

                    public function __construct(string $version)
                    {
                        $this->version = $version;
                    }

                    public function getAttribute($attribute)
                    {
                        return $this->version;
                    }
                };
            }

            public function __destruct()
            {
                unset(self::$pdo[$this->DBID]);
            }
        };
    }

    public function testEscapeLikeWildcards(): void
    {
        $sql = rex_sql::factory();

        static::assertSame('\\%foo\\_bar', $sql->escapeLikeWildcards('%foo_bar'));
    }

    public function testSetGetValue()
    {
        $sql = rex_sql::factory();
        $sql->setTable(self::TABLE);
        $sql->setValue('col_str', 'abc');
        $sql->setValue('col_int', 5);

        static::assertTrue($sql->hasValue('col_str'), 'set value string exists');
        static::assertTrue($sql->hasValue('col_int'), 'set value int exists');

        static::assertEquals('abc', $sql->getValue('col_str'), 'get a previous set string');
        static::assertEquals(5, $sql->getValue('col_int'), 'get a previous set int ');
    }

    public function testSetGetArrayValue()
    {
        $sql = rex_sql::factory();
        $sql->setArrayValue('col_empty_array', []);
        $sql->setArrayValue('col_array', [1, 2, 3]);

        static::assertTrue($sql->hasValue('col_empty_array'), 'set value exists');
        static::assertTrue($sql->hasValue('col_array'), 'set value exists');

        static::assertEquals([], $sql->getArrayValue('col_empty_array'), 'get a previous set empty array');
        static::assertEquals([1, 2, 3], $sql->getArrayValue('col_array'), 'get a previous set array');
    }

    public function testGetValueWithType()
    {
        $sql = rex_sql::factory();
        $sql->setTable(self::TABLE);
        $sql->setValue('col_str', 'abc');
        $sql->setValue('col_int', '5');
        $sql->setArrayValue('col_array', ['foo' => 3, 'bar' => 2]);

        static::assertSame('abc', $sql->getValue('col_str', 'string'));
        static::assertSame(5, $sql->getValue('col_int', 'int'));
        static::assertSame(['foo' => 3, 'baz' => 1], $sql->getValue('col_array', [
            ['foo', 'int', 0],
            ['baz', 'int', 1],
        ]));
    }

    public function testInsertRow()
    {
        $sql = rex_sql::factory();
        $sql->setTable(self::TABLE);
        $sql->setValue('col_int', 5);
        $sql->setValue('col_str', 'abc');
        $sql->setValue('col_text', 'mytext');

        $sql->insert();
        static::assertEquals(1, $sql->getRows());
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
        static::assertEquals(1, $sql->getRows());
    }

    public function testInsertWithoutValues()
    {
        $sql = rex_sql::factory();
        $sql->setTable(self::TABLE);

        $sql->insert();
        static::assertEquals(1, $sql->getRows());
    }

    public function testInsertRecords()
    {
        $sql = rex_sql::factory();
        $sql->setTable(self::TABLE);

        $sql->addRecord(static function (rex_sql $record) {
            $record->setValue('col_str', 'foo');
            $record->setRawValue('col_date', 'UTC_DATE()');
            $record->setValue('col_int', 3);
        });
        $sql->addRecord(static function (rex_sql $record) {
            $record->setValue('col_str', 'bar');
            $record->setDateTimeValue('col_date', strtotime('yesterday'));
            $record->setValue('col_text', 'lorem ipsum');
        });

        $sql->insert();

        static::assertSame(2, $sql->getRows());

        $sql->setTable(self::TABLE)->select();

        static::assertSame(2, $sql->getRows());

        static::assertSame('foo', $sql->getValue('col_str'));
        static::assertSame((new DateTime('now', new DateTimeZone('UTC')))->format('Y-m-d'), $sql->getValue('col_date'));
        static::assertSame('3', $sql->getValue('col_int'));

        $sql->next();

        static::assertSame('bar', $sql->getValue('col_str'));
        static::assertSame(date('Y-m-d', strtotime('yesterday')), $sql->getValue('col_date'));
        static::assertSame('lorem ipsum', $sql->getValue('col_text'));
    }

    public function testInsertOrUpdate()
    {
        $sql = rex_sql::factory();
        $sql->setTable(self::TABLE);
        $sql->setValue('id', 1);
        $sql->setValue('col_int', 5);
        $sql->setValue('col_str', 'abc');

        $sql->insertOrUpdate();
        static::assertEquals(1, $sql->getRows());

        $sql->setTable(self::TABLE)->select();
        static::assertEquals(5, $sql->getValue('col_int'));
        static::assertEquals('abc', $sql->getValue('col_str'));

        $sql->setTable(self::TABLE);
        $sql->setValue('id', 1);
        $sql->setValue('col_int', 3);
        $sql->setValue('col_str', 'foo');

        $sql->insertOrUpdate();
        static::assertEquals(2, $sql->getRows());

        $sql->setTable(self::TABLE)->select();
        static::assertEquals(3, $sql->getValue('col_int'));
        static::assertEquals('foo', $sql->getValue('col_str'));
    }

    public function testInsertOrUpdateRecords()
    {
        $sql = rex_sql::factory();
        $sql->setTable(self::TABLE);

        $sql->addRecord(static function (rex_sql $record) {
            $record->setValue('id', 1);
            $record->setValue('col_str', 'foo');
        });
        $sql->addRecord(static function (rex_sql $record) {
            $record->setValue('id', 2);
            $record->setValue('col_str', 'bar');
        });

        $sql->insertOrUpdate();

        static::assertSame(2, $sql->getRows());

        $sql->setTable(self::TABLE)->select();

        static::assertSame(2, $sql->getRows());

        static::assertSame('1', $sql->getValue('id'));
        static::assertSame('foo', $sql->getValue('col_str'));

        $sql = rex_sql::factory();
        $sql->setTable(self::TABLE);

        $sql->addRecord(static function (rex_sql $record) {
            $record->setValue('id', 1);
            $record->setValue('col_str', 'abc');
        });
        $sql->addRecord(static function (rex_sql $record) {
            $record->setValue('id', 3);
            $record->setValue('col_str', 'baz');
        });

        $sql->insertOrUpdate();

        static::assertSame(3, $sql->getRows());

        $sql->setTable(self::TABLE)->select();

        static::assertSame(3, $sql->getRows());

        static::assertSame('1', $sql->getValue('id'));
        static::assertSame('abc', $sql->getValue('col_str'));

        $sql->next();
        static::assertSame('2', $sql->getValue('id'));
        static::assertSame('bar', $sql->getValue('col_str'));

        $sql->next();
        static::assertSame('3', $sql->getValue('id'));
        static::assertSame('baz', $sql->getValue('col_str'));
    }

    public function testReplaceRecords()
    {
        $sql = rex_sql::factory();
        $sql->setTable(self::TABLE);

        $sql->addRecord(static function (rex_sql $record) {
            $record->setValue('id', 1);
            $record->setValue('col_str', 'foo');
        });
        $sql->addRecord(static function (rex_sql $record) {
            $record->setValue('id', 2);
            $record->setValue('col_str', 'bar');
        });

        $sql->replace();

        static::assertSame(2, $sql->getRows());

        $sql->setTable(self::TABLE)->select();

        static::assertSame(2, $sql->getRows());

        static::assertSame('1', $sql->getValue('id'));
        static::assertSame('foo', $sql->getValue('col_str'));

        $sql = rex_sql::factory();
        $sql->setTable(self::TABLE);

        $sql->addRecord(static function (rex_sql $record) {
            $record->setValue('id', 1);
            $record->setValue('col_str', 'abc');
        });
        $sql->addRecord(static function (rex_sql $record) {
            $record->setValue('id', 3);
            $record->setValue('col_str', 'baz');
        });

        $sql->replace();

        static::assertSame(3, $sql->getRows());

        $sql->setTable(self::TABLE)->select();

        static::assertSame(3, $sql->getRows());

        static::assertSame('1', $sql->getValue('id'));
        static::assertSame('abc', $sql->getValue('col_str'));

        $sql->next();
        static::assertSame('2', $sql->getValue('id'));
        static::assertSame('bar', $sql->getValue('col_str'));

        $sql->next();
        static::assertSame('3', $sql->getValue('id'));
        static::assertSame('baz', $sql->getValue('col_str'));
    }

    public function testUpdateRowByWhereArray()
    {
        // create a row we later update
        $this->testInsertRow();

        $sql = rex_sql::factory();
        $sql->setTable(self::TABLE);
        $sql->setValue('col_str', 'def');
        $sql->setWhere(['col_int' => 5]);
        $sql->setValue('col_int', 6);

        $sql->update();
        static::assertEquals(1, $sql->getRows());

        $sql->setQuery('SELECT * FROM '.self::TABLE);
        static::assertSame('def', $sql->getValue('col_str'));
        static::assertSame('6', $sql->getValue('col_int'));
    }

    public function testUpdateRowByNamedWhere()
    {
        // create a row we later update
        $this->testInsertRow();

        $sql = rex_sql::factory();
        $sql->setTable(self::TABLE);
        $sql->setValue('col_str', 'def');
        $sql->setWhere('col_int = :myint', ['myint' => 5]);

        $sql->update();
        static::assertEquals(1, $sql->getRows());
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
        static::assertEquals(1, $sql->getRows());
    }

    public function testDeleteRow()
    {
        // create a row we later delete
        $this->testInsertRow();

        $sql = rex_sql::factory();
        $sql->setTable(self::TABLE);
        $sql->setWhere(['col_int' => 5]);

        $sql->delete();
        static::assertEquals(1, $sql->getRows());
    }

    public function testGetLastId(): void
    {
        $sql = rex_sql::factory();

        static::assertSame('0', $sql->getLastId(), 'Initial value for LastId');

        $sql->setTable(self::TABLE);
        $sql->setValue('col_int', 5);
        $sql->setValue('col_str', 'abc');
        $sql->setValue('col_text', 'mytext');
        $sql->insert();

        static::assertSame('1', $sql->getLastId(), 'LastId after ->insert()');

        $sql->setTable(self::TABLE);
        $sql->setWhere(['id' => 1]);
        $sql->setValue('col_int', 6);
        $sql->update();

        static::assertSame('0', $sql->getLastId(), 'LastId after ->update()');

        $sql->setQuery('INSERT INTO '.self::TABLE.' SET col_int = 3');

        static::assertSame('2', $sql->getLastId(), 'LastId after second INSERT query');

        $secondSql = rex_sql::factory();
        $secondSql->setQuery('SELECT * FROM '.self::TABLE);

        static::assertSame('0', $secondSql->getLastId(), 'LastId after SELECT query');
        static::assertSame('2', $sql->getLastId(), 'LastId still the same in other sql object');
    }

    public function testGetTables()
    {
        $tables = rex_sql::factory()->getTables();

        static::assertContains(self::TABLE, $tables);
        static::assertNotContains(self::VIEW, $tables);
    }

    public function testShowViews()
    {
        $views = rex_sql::factory()->getViews();

        static::assertNotContains(self::TABLE, $views);
        static::assertContains(self::VIEW, $views);
    }

    public function testShowTablesAndViews()
    {
        $tables = rex_sql::factory()->getTablesAndViews();

        static::assertContains(self::TABLE, $tables);
        static::assertContains(self::VIEW, $tables);
    }

    /**
     * @dataProvider provideGetQueryTypes
     */
    public function testGetQueryType(string $query, $expectedQueryType)
    {
        $actualQueryType = rex_sql::getQueryType($query);
        static::assertSame($expectedQueryType, $actualQueryType);
    }

    public function provideGetQueryTypes(): array
    {
        return [
            ['Select * from testTable', 'SELECT'],
            ['(select * from testTable) union (select * from testTable)', 'SELECT'],
            [' ( SELECT * from testTable)', 'SELECT'],
            ['(DB2) (SELECT * from testTable)', 'SELECT'],
            ['shOW tables', 'SHOW'],
            ['update tableName set field=value', 'UPDATE'],
            ['insert into set field=value', 'INSERT'],
            ['delete from table', 'DELETE'],
            ['rePlace into tableName set field=value', 'REPLACE'],
            ['create tableName', 'CREATE'],
            ['call someprocedure', 'CALL'],
            ['optimize tablename', 'OPTIMIZE'],
            ['dance to the beat :D', false],
        ];
    }
}
