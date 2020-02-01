<?php

use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
class rex_mediapool_functions_test extends TestCase
{
    /**
     * @dataProvider provideIsAllowedMediaType
     */
    public function testIsAllowedMediaType($expected, $filename, array $args = [])
    {
        static::assertSame($expected, rex_mediapool_isAllowedMediaType($filename, $args));
    }

    public function provideIsAllowedMediaType()
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
    public function testIsAllowedMimeType($expected, $path, $filename = null)
    {
        $addon = rex_addon::get('mediapool');

        $whitelist = $addon->getProperty('allowed_mime_types');

        $addon->setProperty('allowed_mime_types', [
            'md' => ['text/plain'],
        ]);

        static::assertSame($expected, rex_mediapool_isAllowedMimeType($path, $filename));

        $addon->setProperty('allowed_mime_types', $whitelist);
    }

    public function provideIsAllowedMimeType()
    {
        return [
            [false, __FILE__],
            [false, __FILE__, 'foo.md'],
            [true, __DIR__.'/../CHANGELOG.md'],
            [false, __DIR__.'/../CHANGELOG.md', 'foo.txt'],
        ];
    }
}
