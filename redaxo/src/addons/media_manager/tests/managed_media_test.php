<?php

use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
class rex_managed_media_test extends TestCase
{
    public function testConstructor()
    {
        $filename = 'CHANGELOG.md';
        $path = rex_path::addon('media_manager', $filename);

        $media = new rex_managed_media($path);

        $this->assertSame($path, $media->getMediaPath());
        $this->assertSame($filename, $media->getMediaFilename());
        $this->assertSame($path, $media->getSourcePath());

        $filename = 'non_existing.jpg';
        $path = rex_path::addon($filename);

        $media = new rex_managed_media($path);

        $this->assertSame($path, $media->getMediaPath());
        $this->assertSame($filename, $media->getMediaFilename());
        $this->assertSame($path, $media->getSourcePath());
        $this->assertFalse($media->exists());
    }
}
