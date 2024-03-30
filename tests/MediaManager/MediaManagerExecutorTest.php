<?php

namespace Redaxo\Core\Tests\MediaManager;

use PHPUnit\Framework\TestCase;
use Redaxo\Core\Filesystem\Path;
use Redaxo\Core\MediaManager\ManagedMedia;

/** @internal */
final class ManagedMediaTest extends TestCase
{
    public function testConstructor(): void
    {
        $filename = 'CHANGELOG.md';
        $path = Path::addon('media_manager', $filename);

        $media = new ManagedMedia($path);

        self::assertSame($path, $media->getMediaPath());
        self::assertSame($filename, $media->getMediaFilename());
        self::assertSame($path, $media->getSourcePath());

        $filename = 'non_existing.jpg';
        $path = Path::addon($filename);

        $media = new ManagedMedia($path);

        self::assertSame($path, $media->getMediaPath());
        self::assertSame($filename, $media->getMediaFilename());
        self::assertSame($path, $media->getSourcePath());
        self::assertFalse($media->exists());
    }
}
