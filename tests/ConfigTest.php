<?php

namespace Redaxo\Core\Tests;

use PHPUnit\Framework\TestCase;
use Redaxo\Core\Config;

/** @internal */
final class ConfigTest extends TestCase
{
    public function testNonExistentConfig(): void
    {
        self::assertFalse(Config::has('test-ns'), 'has() returns false for non-existing namespace');
        self::assertFalse(Config::has('test-ns', 'mykey'), 'has() returns false for non-existing key');
        self::assertFalse(Config::remove('test-ns', 'mykey'), 'remove() returns false when deleting non-existing key');
        self::assertSame([], Config::get('test-ns'), 'get() returns empty array when getting empty namespace');
        self::assertNull(Config::get('test-ns', 'mykey'), 'get() returns null when getting non-existing key');
        self::assertEquals('defaultReturn', Config::get('test-ns', 'mykey', 'defaultReturn'), 'get returns the given default');
    }

    public function testSetGetRemoveConfig(): void
    {
        self::assertFalse(Config::remove('test-ns', 'mykey1'), 'remove() returns false, when deleting an non-existing key');
        self::assertFalse(Config::set('test-ns', 'mykey1', 'myvalA'), 'set() returns false, when config not yet exists');
        self::assertFalse(Config::set('test-ns', ['mykey2' => 'myvalB', 'mykey3' => 'myvalC']), 'set() returns false, when config not yet exists');

        self::assertTrue(Config::has('test-ns'), 'namespace exists after setting a value');
        self::assertTrue(Config::has('test-ns', 'mykey1'), 'the key itself exists');

        self::assertEquals(['mykey1' => 'myvalA', 'mykey2' => 'myvalB', 'mykey3' => 'myvalC'], Config::get('test-ns'), 'get() returns array of stored values');
        self::assertEquals('myvalA', Config::get('test-ns', 'mykey1'), 'get() returns the stored value');

        self::assertTrue(Config::set('test-ns', 'mykey1', 'myval1'), 'set() returns true, when config already exists');
        self::assertTrue(Config::set('test-ns', ['mykey4' => 'myval4', 'mykey2' => 'myval2', 'mykey5' => 'myval5']), 'set() returns true, when config already exists');

        $arr = Config::get('test-ns');
        Config::set('test-ns', $arr);
        self::assertEquals($arr, Config::get('test-ns'), 'set($ns, get($ns)) is idempotent');

        self::assertTrue(Config::remove('test-ns', 'mykey1'), 'remove() returns true, when deleting an existing key');
        Config::remove('test-ns', 'mykey2');
        Config::remove('test-ns', 'mykey3');
        Config::remove('test-ns', 'mykey4');
        Config::remove('test-ns', 'mykey5');

        self::assertFalse(Config::has('test-ns'), 'has() returns false, when checking for empty (non-existing) namespace');
        self::assertFalse(Config::has('test-ns', 'mykey1'), 'has() returns false, when checking for removed key');
        self::assertNull(Config::get('test-ns', 'mykey1'), 'get() returns null, when getting a removed key');
    }

    public function testRemoveNamespace(): void
    {
        Config::set('test-ns', 'mykey1', 'myvalA');
        Config::set('test-ns', 'mykey2', 'myvalB');

        Config::removeNamespace('test-ns');

        self::assertFalse(Config::has('test-ns'), 'removeNamespace() removes the whole namespace');
        self::assertFalse(Config::has('test-ns', 'mykey1'), 'removeNamespace() all keys1');
        self::assertFalse(Config::has('test-ns', 'mykey2'), 'removeNamespace() all keys2');

        self::assertNull(Config::get('test-ns', 'mykey1'), 'removeNamespace() all keys1');
        self::assertNull(Config::get('test-ns', 'mykey2'), 'removeNamespace() all keys2');
    }

    public function testSaveAfterSetAndRemove(): void
    {
        Config::save();

        // if a key was not overwritten, it returns false.
        self::assertFalse(Config::set('test-ns', 'mykey1', 'foo'));
        self::assertTrue(Config::remove('test-ns', 'mykey1'));

        Config::save();
    }
}
