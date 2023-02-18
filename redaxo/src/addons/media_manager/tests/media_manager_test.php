<?php

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
class rex_media_manager_test extends TestCase
{
    public function testGetCacheFilename(): void
    {
        $media = new rex_managed_media(__DIR__.'/foo.jpg');
        $manager = new rex_media_manager($media);

        $cachePath = rex_path::addonCache('media_manager');
        $manager->setCachePath($cachePath);

        $media->setMediaPath(__DIR__.'/bar.gif');

        $property = new ReflectionProperty(rex_media_manager::class, 'type');
        $property->setValue($manager, 'test');

        static::assertSame($cachePath.'test/foo.jpg', $manager->getCacheFilename());
    }

    public function testGetMediaFile(): void
    {
        $_GET['rex_media_file'] = '../foo/bar/baz.jpg';
        static::assertSame('baz.jpg', rex_media_manager::getMediaFile());

        $_GET['rex_media_file'] = '..\\foo\\bar\\baz.jpg';
        static::assertSame('baz.jpg', rex_media_manager::getMediaFile());
    }

    public function testCreate(): void
    {
        $filename = '_media_manager_test.png';
        $path = rex_path::media($filename);

        rex_file::put($path, base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABAQMAAAAl21bKAAAAA1BMVEUAAACnej3aAAAAAXRSTlMAQObYZgAAAApJREFUCNdjYAAAAAIAAeIhvDMAAAAASUVORK5CYII='));

        try {
            $manager = rex_media_manager::create('rex_media_small', $filename);

            static::assertFileExists($manager->getCacheFilename());
            static::assertFileExists($manager->getHeaderCacheFilename());

            $manager = rex_media_manager::create('non_existing_type', $filename);

            static::assertFileDoesNotExist($manager->getCacheFilename());
            static::assertFileDoesNotExist($manager->getHeaderCacheFilename());
        } finally {
            @unlink($path);
        }
    }

    #[DataProvider('dataGetUrl')]
    public function testGetUrl(int|false $expectedBuster, string $type, string|rex_media $file, ?int $timestamp = null): void
    {
        $url = rex_media_manager::getUrl($type, $file, $timestamp);

        if (false === $expectedBuster) {
            static::assertStringNotContainsString('buster=', $url);
        } else {
            static::assertStringContainsString('buster='.$expectedBuster, $url);
        }
    }

    /** @return iterable<int, array{0: false|int, 1: string, 2: string|rex_media, 3?: int}> */
    public static function dataGetUrl(): iterable
    {
        yield [false, 'non_existing', 'test.jpg', time()];

        $media = new class() extends rex_media {
            public int $fakeUpdateDate = 0;

            public function __construct() {}

            public function getFileName()
            {
                return 'test.jpg';
            }

            public function getUpdateDate()
            {
                return $this->fakeUpdateDate;
            }
        };
        $media->fakeUpdateDate = time();

        yield [false, 'non_existing', $media];

        $type = 'rex_media_small';

        yield [false, $type, 'test.jpg'];

        $typeTimestamp = (int) rex_sql::factory()
            ->setQuery('SELECT updatedate FROM '.rex::getTable('media_manager_type').' WHERE name = ?', [$type])
            ->getDateTimeValue('updatedate');

        foreach ([$typeTimestamp - 1000, $typeTimestamp + 1000] as $fileTimestamp) {
            $expectedBuster = max($typeTimestamp, $fileTimestamp);

            yield [$expectedBuster, $type, 'test.jpg', $fileTimestamp];

            $media = clone $media;
            $media->fakeUpdateDate = $fileTimestamp;

            yield [$expectedBuster, $type, $media];
        }
    }
}
