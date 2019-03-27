<?php

use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
class rex_view_test extends TestCase
{
    public function testAddGetCss()
    {
        rex_view::addCssFile('my.css');
        $files = rex_view::getCssFiles()['all'];
        $this->assertTrue('my.css' == end($files));

        rex_view::addCssFile('print.css', 'print');
        $files = rex_view::getCssFiles()['print'];
        $this->assertTrue('print.css' == end($files));
    }

    public function testAddGetJs()
    {
        rex_view::addJsFile('my.js');
        $files = rex_view::getJsFiles();
        $this->assertTrue('my.js' == end($files));
    }

    public function testAddGetJsWithOptions()
    {
        rex_view::addJsFile('my.js');
        $files = rex_view::getJsFilesWithOptions();
        [$file, $options] = end($files);
        $this->assertTrue('my.js' == $file);
        $this->assertTrue($options == [rex_view::JS_IMMUTABLE => false], 'options default to JS_IMMUTABLE=false');

        rex_view::addJsFile('my.js', [rex_view::JS_IMMUTABLE => true]);
        $files = rex_view::getJsFilesWithOptions();
        [$file, $options] = end($files);
        $this->assertTrue('my.js' == $file);
        $this->assertTrue($options == [rex_view::JS_IMMUTABLE => true], 'explicit JS_IMMUTABLE option');

        rex_view::addJsFile('my_async.js', [rex_view::JS_ASYNC => true, rex_view::JS_DEFERED => true]);
        $files = rex_view::getJsFilesWithOptions();
        [$file, $options] = end($files);
        $this->assertTrue('my_async.js' == $file);
        $this->assertTrue($options == [rex_view::JS_ASYNC => true, rex_view::JS_DEFERED => true], 'multiple options');
    }
}
