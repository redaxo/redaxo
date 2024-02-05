<?php

use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
class rex_config_test extends TestCase
{
    public function testNonExistentConfig(): void
    {
        self::assertFalse(rex_config::has('test-ns'), 'has() returns false for non-existing namespace');
        self::assertFalse(rex_config::has('test-ns', 'mykey'), 'has() returns false for non-existing key');
        self::assertFalse(rex_config::remove('test-ns', 'mykey'), 'remove() returns false when deleting non-existing key');
        self::assertSame([], rex_config::get('test-ns'), 'get() returns empty array when getting empty namespace');
        self::assertNull(rex_config::get('test-ns', 'mykey'), 'get() returns null when getting non-existing key');
        self::assertEquals('defaultReturn', rex_config::get('test-ns', 'mykey', 'defaultReturn'), 'get returns the given default');
    }

    public function testSetGetRemoveConfig(): void
    {
        self::assertFalse(rex_config::remove('test-ns', 'mykey1'), 'remove() returns false, when deleting an non-existing key');
        self::assertFalse(rex_config::set('test-ns', 'mykey1', 'myvalA'), 'set() returns false, when config not yet exists');
        self::assertFalse(rex_config::set('test-ns', ['mykey2' => 'myvalB', 'mykey3' => 'myvalC']), 'set() returns false, when config not yet exists');

        self::assertTrue(rex_config::has('test-ns'), 'namespace exists after setting a value');
        self::assertTrue(rex_config::has('test-ns', 'mykey1'), 'the key itself exists');

        self::assertEquals(['mykey1' => 'myvalA', 'mykey2' => 'myvalB', 'mykey3' => 'myvalC'], rex_config::get('test-ns'), 'get() returns array of stored values');
        self::assertEquals('myvalA', rex_config::get('test-ns', 'mykey1'), 'get() returns the stored value');

        self::assertTrue(rex_config::set('test-ns', 'mykey1', 'myval1'), 'set() returns true, when config already exists');
        self::assertTrue(rex_config::set('test-ns', ['mykey4' => 'myval4', 'mykey2' => 'myval2', 'mykey5' => 'myval5']), 'set() returns true, when config already exists');

        $arr = rex_config::get('test-ns');
        rex_config::set('test-ns', $arr);
        self::assertEquals($arr, rex_config::get('test-ns'), 'set($ns, get($ns)) is idempotent');

        self::assertTrue(rex_config::remove('test-ns', 'mykey1'), 'remove() returns true, when deleting an existing key');
        rex_config::remove('test-ns', 'mykey2');
        rex_config::remove('test-ns', 'mykey3');
        rex_config::remove('test-ns', 'mykey4');
        rex_config::remove('test-ns', 'mykey5');

        self::assertFalse(rex_config::has('test-ns'), 'has() returns false, when checking for empty (non-existing) namespace');
        self::assertFalse(rex_config::has('test-ns', 'mykey1'), 'has() returns false, when checking for removed key');
        self::assertNull(rex_config::get('test-ns', 'mykey1'), 'get() returns null, when getting a removed key');
    }

    public function testRemoveNamespace(): void
    {
        rex_config::set('test-ns', 'mykey1', 'myvalA');
        rex_config::set('test-ns', 'mykey2', 'myvalB');

        rex_config::removeNamespace('test-ns');

        self::assertFalse(rex_config::has('test-ns'), 'removeNamespace() removes the whole namespace');
        self::assertFalse(rex_config::has('test-ns', 'mykey1'), 'removeNamespace() all keys1');
        self::assertFalse(rex_config::has('test-ns', 'mykey2'), 'removeNamespace() all keys2');

        self::assertNull(rex_config::get('test-ns', 'mykey1'), 'removeNamespace() all keys1');
        self::assertNull(rex_config::get('test-ns', 'mykey2'), 'removeNamespace() all keys2');
    }

    public function testSaveAfterSetAndRemove(): void
    {
        rex_config::save();

        // if a key was not overwritten, it returns false.
        self::assertFalse(rex_config::set('test-ns', 'mykey1', 'foo'));
        self::assertTrue(rex_config::remove('test-ns', 'mykey1'));

        rex_config::save();
    }
}
