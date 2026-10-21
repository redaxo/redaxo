<?php

namespace Redaxo\Core\Tests\MediaPool;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Redaxo\Core\Content\ContentHandler;
use Redaxo\Core\Database\Sql;
use Redaxo\Core\MediaPool\Media;
use Redaxo\Core\MediaPool\MediaPool;
use Redaxo\Core\MediaPool\MediaPoolCache;

/** @internal */
final class MediaPoolTest extends TestCase
{
    /** @param list<string> $types */
    #[DataProvider('provideIsAllowedExtension')]
    public function testIsAllowedExtension(bool $expected, string $filename, array $types = []): void
    {
        self::assertSame($expected, MediaPool::isAllowedExtension($filename, $types));
    }

    /** @return list<array{0: bool, 1: string, 2?: list<string>}> */
    public static function provideIsAllowedExtension(): array
    {
        return [
            [false, 'foo.bar.php'],
            [false, 'foo.bar.php5'],
            [false, 'foo.bar.php71'],
            [false, 'foo.bar.php_71'],
            [false, 'foo.bar.jsp'],
            [false, '.htaccess'],
            [false, '.htpasswd'],
            [false, 'foo.js.txt'],
            [false, 'foo.js.any.txt'],
            [false, 'shell.php.any.jpg'],
            [false, 'shell.PHP.any.JPG'],
            [false, 'shell.phtml.any.jpg'],
            [true, 'js_datei.txt'],
            [true, 'foo.json'],
            [true, 'foo.any.json'],
            [true, 'php_logo.jpg'],
            [true, 'php_logo.any.jpg'],
            [true, 'foo.bar.png', ['jpg', 'png', 'gif']],
            [false, 'foo.bar.txt', ['jpg', 'png', 'gif']],
            [false, 'foo.bar.php', ['jpg', 'png', 'gif', 'php']],
        ];
    }

    #[DataProvider('provideIsAllowedMimeType')]
    public function testIsAllowedMimeType(bool $expected, string $path, ?string $filename = null): void
    {
        $allowedMimeTypes = MediaPool::$allowedMimeTypes;

        MediaPool::$allowedMimeTypes = [
            'md' => ['text/markdown'],
        ];

        try {
            self::assertSame($expected, MediaPool::isAllowedMimeType($path, $filename));
        } finally {
            MediaPool::$allowedMimeTypes = $allowedMimeTypes;
        }
    }

    /** @return list<array{0: bool, 1: string, 2?: string}> */
    public static function provideIsAllowedMimeType(): array
    {
        return [
            [false, __FILE__],
            [false, __FILE__, 'foo.md'],
            [true, __DIR__ . '/../../SECURITY.md'],
            [false, __DIR__ . '/../../SECURITY.md', 'foo.txt'],
        ];
    }

    /** @param array<string, mixed> $values */
    #[DataProvider('provideFindUsages')]
    public function testFindUsages(bool $expected, string $moduleKey, array $values): void
    {
        $articleId = 2_147_483_647;
        $fileNames = ['rex-test-in-use.jpg', 'rex-test-other.jpg'];

        // the slice needs an article to hang off, see the foreign key of `rex_article_slice`
        Sql::factory()
            ->setTable('rex_article')
            ->setValues(['id' => $articleId, 'parent_id' => null, 'priority' => 1, 'path' => '|'])
            ->addGlobalCreateFields()
            ->addGlobalUpdateFields()
            ->insert();

        try {
            Sql::factory()
                ->setTable('rex_article_translation')
                ->setValues(['article_id' => $articleId, 'language_id' => 1, 'name' => 'test', 'status' => 1])
                ->insert();

            foreach ($fileNames as $fileName) {
                Sql::factory()
                    ->setTable('rex_media')
                    ->setValues(['filename' => $fileName, 'originalname' => $fileName, 'filesize' => 0])
                    ->addGlobalCreateFields()
                    ->addGlobalUpdateFields()
                    ->insert();
            }

            ContentHandler::addSlice($articleId, 1, 1, $moduleKey, $values);

            $media = Media::get($fileNames[0]);
            self::assertNotNull($media);
            self::assertSame($expected, !MediaPool::findUsages($media)->isEmpty());
        } finally {
            // the translation, the slice and its references are removed by the foreign keys
            Sql::factory()->setQuery('DELETE FROM rex_article WHERE id = ?', [$articleId]);
            Sql::factory()->setQuery('DELETE FROM rex_media WHERE filename IN (?, ?)', $fileNames);
            foreach ($fileNames as $fileName) {
                MediaPoolCache::delete($fileName);
            }
        }
    }

    /** @return iterable<string, array{bool, string, array<string, mixed>}> */
    public static function provideFindUsages(): iterable
    {
        yield 'media field' => [true, 'test_fields', ['image' => 'rex-test-in-use.jpg']];
        yield 'media list field' => [true, 'test_fields', ['gallery' => ['rex-test-other.jpg', 'rex-test-in-use.jpg']]];
        yield 'other media' => [false, 'test_fields', ['image' => 'rex-test-other.jpg']];
        yield 'file name in a text field' => [false, 'test_fields', ['headline' => 'rex-test-in-use.jpg']];
        yield 'unknown module' => [false, 'unknown', ['image' => 'rex-test-in-use.jpg']];
    }
}
