<?php

namespace Redaxo\Core\Tests\MediaPool;

use PHPUnit\Framework\TestCase;
use Redaxo\Core\MediaPool\Media;
use ReflectionClass;

/** @internal */
final class rex_media_test extends TestCase
{
    public function testHasValue(): void
    {
        $media = $this->createMediaWithoutConstructor();

        /** @psalm-suppress UndefinedPropertyAssignment */
        $media->med_foo = 'teststring'; // @phpstan-ignore-line

        self::assertTrue($media->hasValue('med_foo'));
        self::assertTrue($media->hasValue('foo'));

        self::assertFalse($media->hasValue('bar'));
        self::assertFalse($media->hasValue('med_bar'));
    }

    public function testGetValue(): void
    {
        $media = $this->createMediaWithoutConstructor();

        /** @psalm-suppress UndefinedPropertyAssignment */
        $media->med_foo = 'teststring'; // @phpstan-ignore-line

        self::assertEquals('teststring', $media->getValue('med_foo'));
        self::assertEquals('teststring', $media->getValue('foo'));

        self::assertNull($media->getValue('bar'));
        self::assertNull($media->getValue('med_bar'));
    }

    private function createMediaWithoutConstructor(): Media
    {
        return (new ReflectionClass(Media::class))->newInstanceWithoutConstructor();
    }
}
