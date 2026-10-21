<?php

namespace Redaxo\Core\Tests\MediaPool;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Redaxo\Core\Content\ContentHandler;
use Redaxo\Core\Database\Sql;
use Redaxo\Core\MediaPool\MediaPool;

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
    #[DataProvider('provideMediaIsInUse')]
    public function testMediaIsInUse(bool $expected, array $values): void
    {
        $articleId = 2_147_483_647;

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

            ContentHandler::addSlice($articleId, 1, 1, 'test', $values);

            self::assertSame($expected, false !== MediaPool::mediaIsInUse('rex-test(1).jpg'));
        } finally {
            // the translation and the slice are removed by the foreign keys
            Sql::factory()
                ->setTable('rex_article')
                ->setWhere(['id' => $articleId])
                ->delete();
        }
    }

    /** @return iterable<string, array{bool, array<string, mixed>}> */
    public static function provideMediaIsInUse(): iterable
    {
        yield 'media field' => [true, ['image' => 'rex-test(1).jpg']];
        yield 'media list field' => [true, ['images' => ['other.jpg', 'rex-test(1).jpg']]];
        yield 'embedded in a text' => [true, ['text' => '<img src="/media/rex-test(1).jpg">']];
        yield 'other media' => [false, ['image' => 'other.jpg']];
        yield 'longer name' => [false, ['image' => 'my-rex-test(1).jpg']];
        yield 'no values' => [false, []];
    }
}
