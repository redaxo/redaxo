<?php

use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
class rex_version_test extends TestCase
{
    public function testIsUnstable()
    {
        $this->assertTrue(rex_version::isUnstable('1.0-dev'));
        $this->assertTrue(rex_version::isUnstable('2.1.0beta'));
        $this->assertTrue(rex_version::isUnstable('1.0b1'));
        $this->assertTrue(rex_version::isUnstable('3.5.1-alpha'));
        $this->assertTrue(rex_version::isUnstable('99.99RC5'));
        $this->assertTrue(rex_version::isUnstable('199.199 RC3'));
        
        $this->assertFalse(rex_version::isUnstable('1.0'));
        $this->assertFalse(rex_version::isUnstable('1.0-final'));
        $this->assertFalse(rex_version::isUnstable('2.45 stable'));
        $this->assertFalse(rex_version::isUnstable('1.0 codename starship'));
    }
    }
