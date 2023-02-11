<?php

use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
class rex_mediapool_test extends TestCase
{
    /**
     * @dataProvider provideIsAllowedExtension
     */
    public function testIsAllowedExtension($expected, $filename, array $args = []): void
    {
        static::assertSame($expected, rex_mediapool::isAllowedExtension($filename, $args));
    }

    /** @return list<array{0: bool, 1: string, 2?: array{types: string}}> */
    public function provideIsAllowedExtension(): array
    {
        return [
            [false, 'foo.bar.php'],
            [false, 'foo.bar.php5'],
            [false, 'foo.bar.php71'],
            [false, 'foo.bar.php_71'],
            [false, 'foo.bar.jsp'],
            [false, '.htaccess'],
            [false, '.htpasswd'],
            [true, 'php_logo.jpg'],
            [true, 'foo.bar.png', ['types' => 'jpg,png,gif']],
            [false, 'foo.bar.txt', ['types' => 'jpg,png,gif']],
            [false, 'foo.bar.php', ['types' => 'jpg,png,gif,php']],
        ];
    }

    /**
     * @dataProvider provideIsAllowedMimeType
     */
    public function testIsAllowedMimeType($expected, $path, $filename = null): void
    {
        $addon = rex_addon::get('mediapool');

        $allowedMimeTypes = $addon->getProperty('allowed_mime_types');

        $addon->setProperty('allowed_mime_types', [
            'md' => ['text/plain'],
        ]);

        static::assertSame($expected, rex_mediapool::isAllowedMimeType($path, $filename));

        $addon->setProperty('allowed_mime_types', $allowedMimeTypes);
    }

    /** @return list<array{0: bool, 1: string, 2?: string}> */
    public function provideIsAllowedMimeType(): array
    {
        return [
            [false, __FILE__],
            [false, __FILE__, 'foo.md'],
            [true, __DIR__.'/../CHANGELOG.md'],
            [false, __DIR__.'/../CHANGELOG.md', 'foo.txt'],
        ];
    }
}
