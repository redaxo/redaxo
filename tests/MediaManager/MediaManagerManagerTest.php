<?php

namespace Redaxo\Core\Tests\MediaManager;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Redaxo\Core\Core;
use Redaxo\Core\Database\Sql;
use Redaxo\Core\Filesystem\File;
use Redaxo\Core\Filesystem\Path;
use Redaxo\Core\MediaManager\MediaManagerExecutor;
use Redaxo\Core\MediaManager\MediaManagerManager;
use ReflectionProperty;
use rex_media;

/** @internal */
final class MediaManagerManagerTest extends TestCase
{
    public function testGetCacheFilename(): void
    {
        $media = new MediaManagerExecutor(__DIR__ . '/foo.jpg');
        $manager = new MediaManagerManager($media);

        $cachePath = Path::addonCache('media_manager');
        $manager->setCachePath($cachePath);

        $media->setMediaPath(__DIR__ . '/bar.gif');

        $property = new ReflectionProperty(MediaManagerManager::class, 'type');
        $property->setValue($manager, 'test');

        self::assertSame($cachePath . 'test/foo.jpg', $manager->getCacheFilename());
    }

    public function testGetMediaFile(): void
    {
        $_GET['rex_media_file'] = '../foo/bar/baz.jpg';
        self::assertSame('baz.jpg', MediaManagerManager::getMediaFile());

        $_GET['rex_media_file'] = '..\\foo\\bar\\baz.jpg';
        self::assertSame('baz.jpg', MediaManagerManager::getMediaFile());
    }

    public function testCreate(): void
    {
        $filename = '_media_manager_test.png';
        $path = Path::media($filename);

        File::put($path, base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABAQMAAAAl21bKAAAAA1BMVEUAAACnej3aAAAAAXRSTlMAQObYZgAAAApJREFUCNdjYAAAAAIAAeIhvDMAAAAASUVORK5CYII='));

        try {
            $manager = MediaManagerManager::create('rex_media_small', $filename);

            self::assertFileExists($manager->getCacheFilename());
            self::assertFileExists($manager->getHeaderCacheFilename());

            $manager = MediaManagerManager::create('non_existing_type', $filename);

            self::assertFileDoesNotExist($manager->getCacheFilename());
            self::assertFileDoesNotExist($manager->getHeaderCacheFilename());
        } finally {
            @unlink($path);
        }
    }

    #[DataProvider('dataGetUrl')]
    public function testGetUrl(int|false $expectedBuster, string $type, string|rex_media $file, ?int $timestamp = null): void
    {
        $url = MediaManagerManager::getUrl($type, $file, $timestamp);

        if (false === $expectedBuster) {
            self::assertStringNotContainsString('buster=', $url);
        } else {
            self::assertStringContainsString('buster=' . $expectedBuster, $url);
        }
    }

    /** @return iterable<int, array{0: false|int, 1: string, 2: string|rex_media, 3?: int}> */
    public static function dataGetUrl(): iterable
    {
        yield [false, 'non_existing', 'test.jpg', time()];

        $media = new class() extends rex_media {
            public int $fakeUpdateDate = 0;

            public function __construct() {}

            public function getFileName(): string
            {
                return 'test.jpg';
            }

            public function getUpdateDate(): int
            {
                return $this->fakeUpdateDate;
            }
        };
        $media->fakeUpdateDate = time();

        yield [false, 'non_existing', $media];

        $type = 'rex_media_small';

        yield [false, $type, 'test.jpg'];

        $typeTimestamp = (int) Sql::factory()
            ->setQuery('SELECT updatedate FROM ' . Core::getTable('media_manager_type') . ' WHERE name = ?', [$type])
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
