<?php

use PHPUnit\Framework\TestCase;
use Redaxo\Core\Filesystem\Path;

/** @internal */
final class rex_managed_media_test extends TestCase
{
    public function testConstructor(): void
    {
        $filename = 'CHANGELOG.md';
        $path = Path::addon('media_manager', $filename);

        $media = new rex_managed_media($path);

        self::assertSame($path, $media->getMediaPath());
        self::assertSame($filename, $media->getMediaFilename());
        self::assertSame($path, $media->getSourcePath());

        $filename = 'non_existing.jpg';
        $path = Path::addon($filename);

        $media = new rex_managed_media($path);

        self::assertSame($path, $media->getMediaPath());
        self::assertSame($filename, $media->getMediaFilename());
        self::assertSame($path, $media->getSourcePath());
        self::assertFalse($media->exists());
    }
}
