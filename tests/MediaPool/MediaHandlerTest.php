<?php

namespace Redaxo\Core\Tests\MediaPool;

use PHPUnit\Framework\TestCase;
use Redaxo\Core\Exception\UserMessageException;
use Redaxo\Core\ExtensionPoint\Extension;
use Redaxo\Core\Filesystem\Dir;
use Redaxo\Core\Filesystem\File;
use Redaxo\Core\Filesystem\Path;
use Redaxo\Core\Fixtures\TestMediaMetaSchema;
use Redaxo\Core\MediaPool\ExtensionPoint\MediaAdding;
use Redaxo\Core\MediaPool\ExtensionPoint\MediaUpdated;
use Redaxo\Core\MediaPool\Media;
use Redaxo\Core\MediaPool\MediaHandler;
use Redaxo\Core\MediaPool\MediaPool;
use Redaxo\Core\Util\Type;
use ReflectionMethod;
use ReflectionProperty;

use function count;

/** @internal */
final class MediaHandlerTest extends TestCase
{
    private const EVIL_SVG = '<svg xmlns="http://www.w3.org/2000/svg"><script>alert(1)</script><circle r="1" onclick="alert(2)"/></svg>';

    protected function setUp(): void
    {
        parent::setUp();

        Dir::create(self::getPath());
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        Dir::delete(self::getPath());
        Dir::delete(self::getTempPath());
    }

    private static function getPath(string $file = ''): string
    {
        return Path::coreData('tests/MediaHandlerTest/' . $file);
    }

    private static function getTempPath(): string
    {
        return Path::coreCache('mediapool/sanitize');
    }

    private static function moveMedia(string $srcFile, string $dstFile, ?string $type): void
    {
        new ReflectionMethod(MediaHandler::class, 'moveMedia')->invoke(null, $srcFile, $dstFile, $type);
    }

    private static function assertSanitized(string $file): void
    {
        $content = File::get($file);

        self::assertIsString($content);
        self::assertStringNotContainsString('<script', $content);
        self::assertStringNotContainsString('onclick', $content);
        self::assertStringContainsString('<circle', $content);
    }

    public function testMoveSanitizesSvg(): void
    {
        $srcFile = self::getPath('source.svg');
        $dstFile = self::getPath('target.svg');
        File::put($srcFile, self::EVIL_SVG);

        self::moveMedia($srcFile, $dstFile, 'image/svg+xml');

        self::assertSanitized($dstFile);
        self::assertFileDoesNotExist($srcFile);
    }

    /**
     * Uploads are sanitized before they are moved, so the source file is a php upload temp file without extension.
     * The svg must be detected via the mime type or via the extension of the target file.
     */
    public function testMoveSanitizesSvgWithoutSourceExtension(): void
    {
        $srcFile = self::getPath('phpUpl04d');
        $dstFile = self::getPath('target.svg');
        File::put($srcFile, self::EVIL_SVG);

        self::moveMedia($srcFile, $dstFile, null);

        self::assertSanitized($dstFile);
    }

    public function testMoveSanitizesSvgInPlace(): void
    {
        $file = self::getPath('inplace.svg');
        File::put($file, self::EVIL_SVG);

        self::moveMedia($file, $file, 'image/svg+xml');

        self::assertSanitized($file);
    }

    public function testMoveRejectsUnparsableSvg(): void
    {
        $srcFile = self::getPath('source.svg');
        $dstFile = self::getPath('target.svg');
        File::put($srcFile, '<svg><unclosed &&&');

        try {
            self::moveMedia($srcFile, $dstFile, 'image/svg+xml');
            self::fail('Expected a UserMessageException');
        } catch (UserMessageException) {
            // expected
        }

        self::assertFileDoesNotExist($dstFile);
    }

    public function testMoveKeepsOtherFilesUntouched(): void
    {
        $srcFile = self::getPath('source.txt');
        $dstFile = self::getPath('target.txt');
        File::put($srcFile, self::EVIL_SVG);

        self::moveMedia($srcFile, $dstFile, 'text/plain');

        self::assertSame(self::EVIL_SVG, File::get($dstFile));
        self::assertFileDoesNotExist($srcFile);
    }

    public function testMoveRemovesTempFile(): void
    {
        $srcFile = self::getPath('source.svg');
        $dstFile = self::getPath('target.svg');
        File::put($srcFile, self::EVIL_SVG);

        self::moveMedia($srcFile, $dstFile, 'image/svg+xml');

        self::assertSame([], glob(self::getTempPath() . '/*') ?: []);
    }

    public function testAddMediaClearsInstancePool(): void
    {
        $srcFile = self::getPath('source.txt');
        File::put($srcFile, 'test');

        $filename = MediaPool::filename('media_handler_test.txt');

        // populate the instance pools, including a cached null for the not yet existing media
        $media = Media::get($filename);
        self::assertNull($media);
        $rootMedia = Media::getRootMedia();

        $addedMedia = MediaHandler::add([
            'category_id' => null,
            'title' => 'test',
            'file' => ['name' => $filename, 'path' => $srcFile],
        ], false);

        try {
            self::assertSame($filename, $addedMedia->fileName);
            self::assertSame($filename, Media::get($filename)?->fileName);

            $rootMediaNames = array_map(static fn (Media $media) => $media->fileName, Media::getRootMedia());
            self::assertCount(count($rootMedia) + 1, $rootMediaNames);
            self::assertContains($filename, $rootMediaNames);
        } finally {
            MediaHandler::delete($filename);
        }
    }

    public function testAddMediaCanBeRejected(): void
    {
        $srcFile = self::getPath('source.txt');
        File::put($srcFile, 'test');

        $filename = MediaPool::filename('media_handler_test.txt');

        $extensions = Type::array(new ReflectionProperty(Extension::class, 'extensions')->getValue());

        try {
            Extension::register(MediaAdding::class, static function (MediaAdding $ep) use ($filename, $srcFile): string {
                self::assertSame($filename, $ep->fileName);
                self::assertSame($srcFile, $ep->path);
                self::assertFalse($ep->isUpload);

                return 'rejected';
            });

            MediaHandler::add([
                'category_id' => null,
                'title' => 'test',
                'file' => ['name' => $filename, 'path' => $srcFile],
            ], false);

            self::fail('Expected a UserMessageException');
        } catch (UserMessageException $e) {
            self::assertSame('rejected', $e->getMessage());
            self::assertNull(Media::get($filename));
            self::assertFileDoesNotExist(Path::media($filename));
        } finally {
            new ReflectionProperty(Extension::class, 'extensions')->setValue(null, $extensions);
        }
    }

    public function testUpdateMediaDispatchesPreviousState(): void
    {
        $srcFile = self::getPath('source.txt');
        File::put($srcFile, 'test');

        $filename = MediaHandler::add([
            'category_id' => null,
            'title' => 'old title',
            'file' => ['name' => MediaPool::filename('media_handler_test.txt'), 'path' => $srcFile],
        ], false)->fileName;

        $extensions = Type::array(new ReflectionProperty(Extension::class, 'extensions')->getValue());

        try {
            $events = [];
            Extension::register(MediaUpdated::class, static function (MediaUpdated $ep) use (&$events): void {
                $events[] = $ep;
            });

            $media = MediaHandler::edit($filename, ['category_id' => null, 'title' => 'new title']);

            self::assertSame('new title', $media->title);
            self::assertCount(1, $events);
            self::assertSame($media, $events[0]->media);
            self::assertSame('old title', $events[0]->previous->title);
        } finally {
            new ReflectionProperty(Extension::class, 'extensions')->setValue(null, $extensions);
            MediaHandler::delete($filename);
        }
    }

    public function testSaveMetaValues(): void
    {
        $srcFile = self::getPath('source.txt');
        File::put($srcFile, 'test');

        $filename = MediaHandler::add([
            'category_id' => null,
            'title' => 'test',
            'file' => ['name' => MediaPool::filename('media_handler_test.txt'), 'path' => $srcFile],
        ], false, meta: ['ai_generated' => true, 'alt' => 'old alt'])->fileName;

        try {
            $meta = TestMediaMetaSchema::of(Type::notNull(Media::get($filename)));
            self::assertTrue($meta->aiGenerated);
            self::assertSame('old alt', $meta->alt);

            // values missing in the array are kept
            $media = MediaHandler::edit($filename, ['category_id' => null, 'title' => 'test'], meta: ['alt' => 'new alt']);

            $meta = TestMediaMetaSchema::of($media);
            self::assertTrue($meta->aiGenerated);
            self::assertSame('new alt', $meta->alt);
        } finally {
            MediaHandler::delete($filename);
        }
    }
}
