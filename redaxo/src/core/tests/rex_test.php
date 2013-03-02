<?php
class rex_rex_test extends PHPUnit_Framework_TestCase
{
    public function testRexConfig()
    {
        $key = 'aTestKey';
        // initial test on empty config
        $this->assertFalse(rex::hasConfig($key), 'the key does not exists at first');
        $this->assertNull(rex::getConfig($key), 'getting non existing key returns null');
        $this->assertEquals(rex::getConfig($key, 'defVal'), 'defVal', 'getting non existing key returns a given default');
        $this->assertFalse(rex::removeConfig($key), 'remove non existing key returns false');

        // test after setting a value
        $this->assertFalse(rex::setConfig($key, 'aVal'), 'setting non-existant value returns false');
        $this->assertEquals(rex::getConfig($key, 'defVal'), 'aVal', 'getting existing key returns its value');
        $this->assertTrue(rex::hasConfig($key), 'setted value exists');

        // test after re-setting a value
        $this->assertTrue(rex::setConfig($key, 'aOtherVal'), 're-setting a value returns true');
        $this->assertEquals(rex::getConfig($key, 'defaOtherVal'), 'aOtherVal', 'getting existing key returns its value');

        // test after cleanup
        $this->assertTrue(rex::removeConfig($key), 'remove a existing key returns true');
        $this->assertFalse(rex::hasConfig($key), 'the key does not exists after removal');
        $this->assertNull(rex::getConfig($key), 'getting non existing key returns null');
        $this->assertEquals(rex::getConfig($key, 'defVal'), 'defVal', 'getting non existing key returns a given default');
    }

    public function testRexProperty()
    {
        $key = 'aTestKey';
        // initial test on empty config
        $this->assertFalse(rex::hasProperty($key), 'the key does not exists at first');
        $this->assertNull(rex::getProperty($key), 'getting non existing key returns null');
        $this->assertEquals(rex::getProperty($key, 'defVal'), 'defVal', 'getting non existing key returns a given default');
        $this->assertFalse(rex::removeProperty($key), 'remove non existing key returns false');

        // test after setting a value
        $this->assertFalse(rex::setProperty($key, 'aVal'), 'setting non-existant value returns false');
        $this->assertEquals(rex::getProperty($key, 'defVal'), 'aVal', 'getting existing key returns its value');
        $this->assertTrue(rex::hasProperty($key), 'setted value exists');

        // test after re-setting a value
        $this->assertTrue(rex::setProperty($key, 'aOtherVal'), 're-setting a value returns true');
        $this->assertEquals(rex::getProperty($key, 'defaOtherVal'), 'aOtherVal', 'getting existing key returns its value');

        // test after cleanup
        $this->assertTrue(rex::removeProperty($key), 'remove a existing key returns true');
        $this->assertFalse(rex::hasProperty($key), 'the key does not exists after removal');
        $this->assertNull(rex::getProperty($key), 'getting non existing key returns null');
        $this->assertEquals(rex::getProperty($key, 'defVal'), 'defVal', 'getting non existing key returns a given default');
    }

    public function testIsSetup()
    {
        $this->assertFalse(rex::isSetup(), 'test run not within the setup');
        // TODO find more appropriate tests
    }

    public function testIsBackend()
    {
        $this->assertTrue(rex::isBackend(), 'test run in the backend');
        // TODO find more appropriate tests
    }

    public function testGetTablePrefix()
    {
        $this->assertEquals(rex::getTablePrefix(), 'rex_', 'table prefix defauts to rex_');
    }

    public function testGetTable()
    {
        $this->assertEquals(rex::getTable('mytable'), 'rex_mytable', 'tablename gets properly prefixed');
    }

    public function testGetTempPrefix()
    {
        $this->assertEquals(rex::getTempPrefix(), 'tmp_', 'temp prefix defaults to tmp_');
    }

    public function testGetUser()
    {
        // there is no user, when tests are run from CLI
        if (PHP_SAPI === 'cli') return;

        $this->assertNotNull(rex::getUser(), 'user is not null');
        $this->assertInstanceOf('rex_user', rex::getUser(), 'returns a user of correct class');
    }

    public function testGetVersion()
    {
        $this->assertTrue(rex::getVersion() != '', 'a version string is returned');
        $vers = rex::getVersion();
        $versParts = explode('.', $vers);
        $this->assertTrue($versParts[0] == 5, 'the major version is 5');
    }
}
