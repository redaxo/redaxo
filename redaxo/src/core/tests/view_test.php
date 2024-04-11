<?php

use PHPUnit\Framework\TestCase;

/** @internal */
final class rex_view_test extends TestCase
{
    public function testAddGetCss(): void
    {
        rex_view::addCssFile('my.css');
        $files = rex_view::getCssFiles()['all'];
        self::assertTrue('my.css' == end($files));

        rex_view::addCssFile('print.css', 'print');
        $files = rex_view::getCssFiles()['print'];
        self::assertTrue('print.css' == end($files));
    }

    public function testAddGetJs(): void
    {
        rex_view::addJsFile('my.js');
        $files = rex_view::getJsFiles();
        self::assertTrue('my.js' == end($files));
    }

    public function testAddGetJsWithOptions(): void
    {
        rex_view::addJsFile('my.js');
        $files = rex_view::getJsFilesWithOptions();
        [$file, $options] = end($files);
        self::assertTrue('my.js' == $file);
        self::assertTrue($options == [rex_view::JS_IMMUTABLE => false], 'options default to JS_IMMUTABLE=false');

        rex_view::addJsFile('my.js', [rex_view::JS_IMMUTABLE => true]);
        $files = rex_view::getJsFilesWithOptions();
        [$file, $options] = end($files);
        self::assertTrue('my.js' == $file);
        self::assertTrue($options == [rex_view::JS_IMMUTABLE => true], 'explicit JS_IMMUTABLE option');

        rex_view::addJsFile('my_async.js', [rex_view::JS_ASYNC => true, rex_view::JS_DEFERED => true]);
        $files = rex_view::getJsFilesWithOptions();
        [$file, $options] = end($files);
        self::assertTrue('my_async.js' == $file);
        self::assertTrue($options == [rex_view::JS_ASYNC => true, rex_view::JS_DEFERED => true], 'multiple options');
    }
}
