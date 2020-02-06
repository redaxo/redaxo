<?php

use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
class rex_media_manager_test extends TestCase
{
    public function testGetCacheFilename()
    {
        $media = new rex_managed_media(__DIR__.'/foo.jpg');
        $manager = new rex_media_manager($media);

        $cachePath = rex_path::addonCache('media_manager');
        $manager->setCachePath($cachePath);

        $media->setMediaPath(__DIR__.'/bar.gif');

        $property = new ReflectionProperty(rex_media_manager::class, 'type');
        $property->setAccessible(true);
        $property->setValue($manager, 'test');

        static::assertSame($cachePath.'test/foo.jpg', $manager->getCacheFilename());
    }

    public function testGetMediaFile()
    {
        $_GET['rex_media_file'] = '../foo/bar/baz.jpg';
        static::assertSame('baz.jpg', rex_media_manager::getMediaFile());

        $_GET['rex_media_file'] = '..\\foo\\bar\\baz.jpg';
        static::assertSame('baz.jpg', rex_media_manager::getMediaFile());
    }

    public function testCreate()
    {
        $filename = '_media_manager_test.png';
        $path = rex_path::media($filename);

        rex_file::put($path, base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABAQMAAAAl21bKAAAAA1BMVEUAAACnej3aAAAAAXRSTlMAQObYZgAAAApJREFUCNdjYAAAAAIAAeIhvDMAAAAASUVORK5CYII='));

        try {
            $manager = rex_media_manager::create('rex_mediapool_preview', $filename);

            static::assertFileExists($manager->getCacheFilename());
            static::assertFileExists($manager->getHeaderCacheFilename());

            $manager = rex_media_manager::create('non_existing_type', $filename);

            static::assertFileNotExists($manager->getCacheFilename());
            static::assertFileNotExists($manager->getHeaderCacheFilename());
        } finally {
            @unlink($path);
        }
    }

    /**
     * @dataProvider dataGetUrl
     */
    public function testGetUrl($expectedBuster, $type, $file, $timestamp = null)
    {
        $url = rex_media_manager::getUrl($type, $file, $timestamp);

        if (false === $expectedBuster) {
            static::assertNotContains('buster=', $url);
        } else {
            static::assertContains('buster='.$expectedBuster, $url);
        }
    }

    public function dataGetUrl()
    {
        yield [false, 'non_existing', 'test.jpg', time()];

        $media = $this->getMockBuilder(rex_media::class)->disableOriginalConstructor()->getMock();
        $media->method('getFileName')->willReturn('test.jpg');
        $media->method('getUpdatedate')->willReturn(time());

        yield [false, 'non_existing', $media];

        $type = 'rex_mediapool_preview';

        yield [false, $type, 'test.jpg'];

        $typeTimestamp = rex_sql::factory()
            ->setQuery('SELECT updatedate FROM '.rex::getTable('media_manager_type').' WHERE name = ?', [$type])
            ->getDateTimeValue('updatedate');

        foreach ([$typeTimestamp - 1000, $typeTimestamp + 1000] as $fileTimestamp) {
            $expectedBuster = max($typeTimestamp, $fileTimestamp);

            yield [$expectedBuster, $type, 'test.jpg', $fileTimestamp];

            $media = $this->getMockBuilder(rex_media::class)->disableOriginalConstructor()->getMock();
            $media->method('getFileName')->willReturn('test.jpg');
            $media->method('getUpdatedate')->willReturn($fileTimestamp);

            yield [$expectedBuster, $type, $media];
        }
    }
}
