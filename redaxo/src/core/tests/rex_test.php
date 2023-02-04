<?php

use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
class rex_rex_test extends TestCase
{
    public function testRexConfig(): void
    {
        $key = 'aTestKey:'. __METHOD__;
        // initial test on empty config
        static::assertFalse(rex::hasConfig($key), 'the key does not exists at first');
        static::assertNull(rex::getConfig($key), 'getting non existing key returns null');
        static::assertEquals(rex::getConfig($key, 'defVal'), 'defVal', 'getting non existing key returns a given default');
        static::assertFalse(rex::removeConfig($key), 'remove non existing key returns false');

        // test after setting a value
        static::assertFalse(rex::setConfig($key, 'aVal'), 'setting non-existant value returns false');
        static::assertEquals(rex::getConfig($key, 'defVal'), 'aVal', 'getting existing key returns its value');
        static::assertTrue(rex::hasConfig($key), 'setted value exists');

        // test after re-setting a value
        static::assertTrue(rex::setConfig($key, 'aOtherVal'), 're-setting a value returns true');
        static::assertEquals(rex::getConfig($key, 'defaOtherVal'), 'aOtherVal', 'getting existing key returns its value');

        // test after cleanup
        static::assertTrue(rex::removeConfig($key), 'remove a existing key returns true');
        static::assertFalse(rex::hasConfig($key), 'the key does not exists after removal');
        static::assertNull(rex::getConfig($key), 'getting non existing key returns null');
        static::assertEquals(rex::getConfig($key, 'defVal'), 'defVal', 'getting non existing key returns a given default');
    }

    public function testRexProperty(): void
    {
        $key = 'aTestKey:'. __METHOD__;
        // initial test on empty config
        static::assertFalse(rex::hasProperty($key), 'the key does not exists at first');
        static::assertNull(rex::getProperty($key), 'getting non existing key returns null');
        static::assertEquals(rex::getProperty($key, 'defVal'), 'defVal', 'getting non existing key returns a given default');
        static::assertFalse(rex::removeProperty($key), 'remove non existing key returns false');

        // test after setting a value
        static::assertFalse(rex::setProperty($key, 'aVal'), 'setting non-existant value returns false');
        static::assertEquals(rex::getProperty($key, 'defVal'), 'aVal', 'getting existing key returns its value');
        static::assertTrue(rex::hasProperty($key), 'setted value exists');

        // test after re-setting a value
        static::assertTrue(rex::setProperty($key, 'aOtherVal'), 're-setting a value returns true');
        static::assertEquals(rex::getProperty($key, 'defaOtherVal'), 'aOtherVal', 'getting existing key returns its value');

        // test after cleanup
        static::assertTrue(rex::removeProperty($key), 'remove a existing key returns true');
        static::assertFalse(rex::hasProperty($key), 'the key does not exists after removal');
        static::assertNull(rex::getProperty($key), 'getting non existing key returns null');
        static::assertEquals(rex::getProperty($key, 'defVal'), 'defVal', 'getting non existing key returns a given default');
    }

    public function testIsSetup(): void
    {
        static::assertFalse(rex::isSetup(), 'test run not within the setup');
        // TODO find more appropriate tests
    }

    public function testIsBackend(): void
    {
        static::assertTrue(rex::isBackend(), 'test run in the backend');
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

            static::assertFalse(rex::isDebugMode());
            static::assertSame($debug, rex::getDebugFlags());

            rex::setProperty('debug', true);

            static::assertTrue(rex::isDebugMode());
            static::assertArrayHasKey('throw_always_exception', rex::getDebugFlags());
            static::assertFalse(rex::getDebugFlags()['throw_always_exception']);

            rex::setProperty('debug', ['enabled' => false]);

            static::assertFalse(rex::isDebugMode());
            static::assertArrayHasKey('throw_always_exception', rex::getDebugFlags());
            static::assertFalse(rex::getDebugFlags()['throw_always_exception']);

            $debug = [
                'enabled' => true,
                'throw_always_exception' => true,
            ];
            rex::setProperty('debug', $debug);
            static::assertSame($debug, rex::getDebugFlags());

            $debug = [
                'enabled' => true,
                'throw_always_exception' => E_WARNING | E_NOTICE,
            ];
            rex::setProperty('debug', $debug);
            static::assertSame($debug, rex::getDebugFlags());

            rex::setProperty('debug', [
                'enabled' => true,
                'throw_always_exception' => ['E_WARNING', 'E_NOTICE'],
            ]);
            static::assertSame($debug, rex::getDebugFlags());
        } finally {
            rex::setProperty('debug', $orgDebug);
        }
    }

    public function testGetTablePrefix(): void
    {
        static::assertEquals(rex::getTablePrefix(), 'rex_', 'table prefix defauts to rex_');
    }

    public function testGetTable(): void
    {
        static::assertEquals(rex::getTable('mytable'), 'rex_mytable', 'tablename gets properly prefixed');
    }

    public function testGetTempPrefix(): void
    {
        static::assertEquals(rex::getTempPrefix(), 'tmp_', 'temp prefix defaults to tmp_');
    }

    public function testGetServer(): void
    {
        $origServer = rex::getProperty('server');

        try {
            rex::setProperty('server', 'http://www.redaxo.org');
            static::assertEquals('http://www.redaxo.org/', rex::getServer());
            static::assertEquals('https://www.redaxo.org/', rex::getServer('https'));
            static::assertEquals('www.redaxo.org/', rex::getServer(''));
        } finally {
            rex::setProperty('server', $origServer);
        }
    }

    public function testGetVersion(): void
    {
        static::assertTrue('' != rex::getVersion(), 'a version string is returned');
        $vers = rex::getVersion();
        $versParts = explode('.', $vers);
        static::assertTrue(5 == $versParts[0], 'the major version is 5');
    }
}
