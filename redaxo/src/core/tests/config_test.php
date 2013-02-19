<?php
class rex_config_test extends PHPUnit_Framework_TestCase
{
    public function testNonExistentConfig()
    {
        $this->assertFalse(rex_config::has('test-ns'), 'has() returns false for non-existing namespace');
        $this->assertFalse(rex_config::has('test-ns', 'mykey'), 'has() returns false for non-existing key');
        $this->assertFalse(rex_config::remove('test-ns', 'mykey'), 'remove() returns false when deleting non-existing key');
        $this->assertSame(array(), rex_config::get('test-ns'), 'get() returns empty array when getting empty namespace');
        $this->assertNull(rex_config::get('test-ns', 'mykey'), 'get() returns null when getting non-existing key');
        $this->assertEquals('defaultReturn', rex_config::get('test-ns', 'mykey', 'defaultReturn'), 'get returns the given default');
    }

    public function testSetGetRemoveConfig()
    {
        $this->assertFalse(rex_config::remove('test-ns', 'mykey1'), 'remove() returns false, when deleting an non-existing key');
        $this->assertFalse(rex_config::set('test-ns', 'mykey1', 'myvalA'), 'set() returns false, when config not yet exists');
        $this->assertFalse(rex_config::set('test-ns', array('mykey2' => 'myvalB', 'mykey3' => 'myvalC')), 'set() returns false, when config not yet exists');

        $this->assertTrue(rex_config::has('test-ns'), 'namespace exists after setting a value');
        $this->assertTrue(rex_config::has('test-ns', 'mykey1'), 'the key itself exists');

        $this->assertEquals(array('mykey1' => 'myvalA', 'mykey2' => 'myvalB', 'mykey3' => 'myvalC'), rex_config::get('test-ns'), 'get() returns array of stored values');
        $this->assertEquals('myvalA', rex_config::get('test-ns', 'mykey1'), 'get() returns the stored value');

        $this->assertTrue(rex_config::set('test-ns', 'mykey1', 'myval1'), 'set() returns true, when config already exists');
        $this->assertTrue(rex_config::set('test-ns', array('mykey4' => 'myval4', 'mykey2' => 'myval2', 'mykey5' => 'myval5')), 'set() returns true, when config already exists');

        $arr = rex_config::get('test-ns');
        rex_config::set('test-ns', $arr);
        $this->assertEquals($arr, rex_config::get('test-ns'), 'set($ns, get($ns)) is idempotent');

        $this->assertTrue(rex_config::remove('test-ns', 'mykey1'), 'remove() returns true, when deleting an existing key');
        rex_config::remove('test-ns', 'mykey2');
        rex_config::remove('test-ns', 'mykey3');
        rex_config::remove('test-ns', 'mykey4');
        rex_config::remove('test-ns', 'mykey5');

        $this->assertFalse(rex_config::has('test-ns'), 'has() returns false, when checking for empty (non-existing) namespace');
        $this->assertFalse(rex_config::has('test-ns', 'mykey1'), 'has() returns false, when checking for removed key');
        $this->assertNull(rex_config::get('test-ns', 'mykey1'), 'get() returns null, when getting a removed key');
    }

    public function testRemoveNamespace()
    {
        rex_config::set('test-ns', 'mykey1', 'myvalA');
        rex_config::set('test-ns', 'mykey2', 'myvalB');

        rex_config::removeNamespace('test-ns');

        $this->assertFalse(rex_config::has('test-ns'), 'removeNamespace() removes the whole namespace');
        $this->assertFalse(rex_config::has('test-ns', 'mykey1'), 'removeNamespace() all keys1');
        $this->assertFalse(rex_config::has('test-ns', 'mykey2'), 'removeNamespace() all keys2');

        $this->assertNull(rex_config::get('test-ns', 'mykey1'), 'removeNamespace() all keys1');
        $this->assertNull(rex_config::get('test-ns', 'mykey2'), 'removeNamespace() all keys2');
    }
}
