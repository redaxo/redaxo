<?php

use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
class rex_rex_test extends TestCase
{
    public function testRexConfig(): void
    {
        $key = 'aTestKey:' . __METHOD__;
        // initial test on empty config
        self::assertFalse(rex::hasConfig($key), 'the key does not exists at first');
        self::assertNull(rex::getConfig($key), 'getting non existing key returns null');
        self::assertEquals(rex::getConfig($key, 'defVal'), 'defVal', 'getting non existing key returns a given default');
        self::assertFalse(rex::removeConfig($key), 'remove non existing key returns false');

        // test after setting a value
        self::assertFalse(rex::setConfig($key, 'aVal'), 'setting non-existant value returns false');
        self::assertEquals(rex::getConfig($key, 'defVal'), 'aVal', 'getting existing key returns its value');
        self::assertTrue(rex::hasConfig($key), 'setted value exists');

        // test after re-setting a value
        self::assertTrue(rex::setConfig($key, 'aOtherVal'), 're-setting a value returns true');
        self::assertEquals(rex::getConfig($key, 'defaOtherVal'), 'aOtherVal', 'getting existing key returns its value');

        // test after cleanup
        self::assertTrue(rex::removeConfig($key), 'remove a existing key returns true');
        self::assertFalse(rex::hasConfig($key), 'the key does not exists after removal');
        self::assertNull(rex::getConfig($key), 'getting non existing key returns null');
        self::assertEquals(rex::getConfig($key, 'defVal'), 'defVal', 'getting non existing key returns a given default');
    }

    public function testRexProperty(): void
    {
        $key = 'aTestKey:' . __METHOD__;
        // initial test on empty config
        self::assertFalse(rex::hasProperty($key), 'the key does not exists at first');
        self::assertNull(rex::getProperty($key), 'getting non existing key returns null');
        self::assertEquals(rex::getProperty($key, 'defVal'), 'defVal', 'getting non existing key returns a given default');
        self::assertFalse(rex::removeProperty($key), 'remove non existing key returns false');

        // test after setting a value
        self::assertFalse(rex::setProperty($key, 'aVal'), 'setting non-existant value returns false');
        self::assertEquals(rex::getProperty($key, 'defVal'), 'aVal', 'getting existing key returns its value');
        self::assertTrue(rex::hasProperty($key), 'setted value exists');

        // test after re-setting a value
        self::assertTrue(rex::setProperty($key, 'aOtherVal'), 're-setting a value returns true');
        self::assertEquals(rex::getProperty($key, 'defaOtherVal'), 'aOtherVal', 'getting existing key returns its value');

        // test after cleanup
        self::assertTrue(rex::removeProperty($key), 'remove a existing key returns true');
        self::assertFalse(rex::hasProperty($key), 'the key does not exists after removal');
        self::assertNull(rex::getProperty($key), 'getting non existing key returns null');
        self::assertEquals(rex::getProperty($key, 'defVal'), 'defVal', 'getting non existing key returns a given default');
    }

    public function testIsSetup(): void
    {
        self::assertFalse(rex::isSetup(), 'test run not within the setup');
        // TODO find more appropriate tests
    }

    public function testIsBackend(): void
    {
        self::assertTrue(rex::isBackend(), 'test run in the backend');
        // TODO find more appropriate tests
    }

    public function testDebugFlags(): void
    {
        $orgDebug = rex::getProperty('debug');
        try {
            $debug = [
                'enabled' => false,
                'throw_always_exception' => false,
            ];
            rex::setProperty('debug', $debug);

            self::assertFalse(rex::isDebugMode());
            self::assertSame($debug, rex::getDebugFlags());

            rex::setProperty('debug', true);

            self::assertTrue(rex::isDebugMode());
            self::assertArrayHasKey('throw_always_exception', rex::getDebugFlags());
            self::assertFalse(rex::getDebugFlags()['throw_always_exception']);

            rex::setProperty('debug', ['enabled' => false]);

            self::assertFalse(rex::isDebugMode());
            self::assertArrayHasKey('throw_always_exception', rex::getDebugFlags());
            self::assertFalse(rex::getDebugFlags()['throw_always_exception']);

            $debug = [
                'enabled' => true,
                'throw_always_exception' => true,
            ];
            rex::setProperty('debug', $debug);
            self::assertSame($debug, rex::getDebugFlags());

            $debug = [
                'enabled' => true,
                'throw_always_exception' => E_WARNING | E_NOTICE,
            ];
            rex::setProperty('debug', $debug);
            self::assertSame($debug, rex::getDebugFlags());

            rex::setProperty('debug', [
                'enabled' => true,
                'throw_always_exception' => ['E_WARNING', 'E_NOTICE'],
            ]);
            self::assertSame($debug, rex::getDebugFlags());
        } finally {
            rex::setProperty('debug', $orgDebug);
        }
    }

    public function testGetTablePrefix(): void
    {
        self::assertEquals(rex::getTablePrefix(), 'rex_', 'table prefix defauts to rex_');
    }

    public function testGetTable(): void
    {
        self::assertEquals(rex::getTable('mytable'), 'rex_mytable', 'tablename gets properly prefixed');
    }

    public function testGetTempPrefix(): void
    {
        self::assertEquals(rex::getTempPrefix(), 'tmp_', 'temp prefix defaults to tmp_');
    }

    public function testGetServer(): void
    {
        $origServer = rex::getProperty('server');

        try {
            rex::setProperty('server', 'http://www.redaxo.org');
            self::assertEquals('http://www.redaxo.org/', rex::getServer());
            self::assertEquals('https://www.redaxo.org/', rex::getServer('https'));
            self::assertEquals('www.redaxo.org/', rex::getServer(''));
        } finally {
            rex::setProperty('server', $origServer);
        }
    }

    public function testGetVersion(): void
    {
        self::assertTrue('' != rex::getVersion(), 'a version string is returned');
        $vers = rex::getVersion();
        $versParts = explode('.', $vers);
        self::assertTrue(6 == $versParts[0], 'the major version is 6');
    }
}
