<?php

use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
class rex_managed_media_test extends TestCase
{
    public function testConstructor(): void
    {
        $filename = 'CHANGELOG.md';
        $path = rex_path::addon('media_manager', $filename);

        $media = new rex_managed_media($path);

        static::assertSame($path, $media->getMediaPath());
        static::assertSame($filename, $media->getMediaFilename());
        static::assertSame($path, $media->getSourcePath());

        $filename = 'non_existing.jpg';
        $path = rex_path::addon($filename);

        $media = new rex_managed_media($path);

        static::assertSame($path, $media->getMediaPath());
        static::assertSame($filename, $media->getMediaFilename());
        static::assertSame($path, $media->getSourcePath());
        static::assertFalse($media->exists());
    }
}
