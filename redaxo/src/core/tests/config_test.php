<?php
class rex_config_test extends PHPUnit_Framework_TestCase
{
  public function testNonExistentConfig()
  {
    $this->assertFalse(rex_config::has('test-ns', 'mykey'), 'has() returns false for non-existing key');
    $this->assertFalse(rex_config::remove('test-ns', 'mykey'), 'remove() returns false when deleting non-existing key');
    $this->assertNull(rex_config::get('test-ns', 'mykey'), 'get() returns null when getting non-existing key');
    $this->assertEquals('defaultReturn', rex_config::get('test-ns', 'mykey', 'defaultReturn'), 'get returns the given default');
  }

  public function testSetGetConfig()
  {
    $this->assertFalse(rex_config::remove('test-ns', 'mykey'), 'remove() returns false, when deleting an non-existing key');
    $this->assertFalse(rex_config::set('test-ns', 'mykey', 'myval'), 'set() returns false, when config not yet exists');

    $this->assertTrue(rex_config::has('test-ns'), 'namespace exists after setting a value');
    $this->assertTrue(rex_config::has('test-ns', 'mykey'), 'the key itself exists');

    $this->assertEquals('myval', rex_config::get('test-ns', 'mykey'), 'get() returns the stored value');

    $this->assertTrue(rex_config::set('test-ns', 'mykey', 'myval2'), 'set() returns true, when config already exists');
    $this->assertTrue(rex_config::remove('test-ns', 'mykey'), 'remove() returns true, when deleting an existing key');

    // TODO ? (aktuell wird ein namespace nicht gelöscht wenn man den letzten schlüssel darin entfernt)
    $this->assertTrue(rex_config::has('test-ns'));
    $this->assertFalse(rex_config::has('test-ns', 'mykey'), 'has() returns false, when checking for removed key');
    $this->assertNull(rex_config::get('test-ns', 'mykey'), 'get() returns null, when getting a removed key');
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
