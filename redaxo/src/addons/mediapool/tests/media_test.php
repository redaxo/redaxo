<?php

use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
class rex_media_test extends TestCase
{
    public function testHasValue()
    {
        $mediaClass = new ReflectionClass(rex_media::class);
        /** @var rex_media $media */
        $media = $mediaClass->newInstanceWithoutConstructor();

        $media->med_foo = 'teststring';

        $this->assertTrue($media->hasValue('med_foo'));
        $this->assertTrue($media->hasValue('foo'));

        $this->assertFalse($media->hasValue('bar'));
        $this->assertFalse($media->hasValue('med_bar'));
    }

    public function testGetValue()
    {
        $mediaClass = new ReflectionClass(rex_media::class);
        /** @var rex_media $media */
        $media = $mediaClass->newInstanceWithoutConstructor();

        $media->med_foo = 'teststring';

        $this->assertEquals('teststring', $media->getValue('med_foo'));
        $this->assertEquals('teststring', $media->getValue('foo'));

        $this->assertNull($media->getValue('bar'));
        $this->assertNull($media->getValue('med_bar'));
    }
}
