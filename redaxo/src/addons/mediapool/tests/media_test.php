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

        static::assertTrue($media->hasValue('med_foo'));
        static::assertTrue($media->hasValue('foo'));

        static::assertFalse($media->hasValue('bar'));
        static::assertFalse($media->hasValue('med_bar'));
    }

    public function testGetValue()
    {
        $mediaClass = new ReflectionClass(rex_media::class);
        /** @var rex_media $media */
        $media = $mediaClass->newInstanceWithoutConstructor();

        $media->med_foo = 'teststring';

        static::assertEquals('teststring', $media->getValue('med_foo'));
        static::assertEquals('teststring', $media->getValue('foo'));

        static::assertNull($media->getValue('bar'));
        static::assertNull($media->getValue('med_bar'));
    }
}
