<?php

class rex_view_test extends PHPUnit_Framework_TestCase
{
    public function testAddGetCss()
    {
        rex_view::addCssFile('my.css');
        $files = rex_view::getCssFiles()['all'];
        $this->assertTrue(end($files) == 'my.css');

        rex_view::addCssFile('print.css', 'print');
        $files = rex_view::getCssFiles()['print'];
        $this->assertTrue(end($files) == 'print.css');
    }

    public function testAddGetJs()
    {
        rex_view::addJsFile('my.js');
        $files = rex_view::getJsFiles();
        $this->assertTrue(end($files) == 'my.js');
    }

    public function testAddGetJsWithOptions()
    {
        rex_view::addJsFile('my.js');
        $files = rex_view::getJsFilesWithOptions();
        list($file, $options) = end($files);
        $this->assertTrue($file == 'my.js');
        $this->assertTrue($options == [rex_view::JS_IMMUTABLE => true], 'options default to JS_IMMUTABLE');

        rex_view::addJsFile('my.js', [rex_view::JS_IMMUTABLE => true]);
        $files = rex_view::getJsFilesWithOptions();
        list($file, $options) = end($files);
        $this->assertTrue($file == 'my.js');
        $this->assertTrue($options == [rex_view::JS_IMMUTABLE => true], 'explicit JS_IMMUTABLE option');

        rex_view::addJsFile('my_async.js', [rex_view::JS_ASYNC => true, rex_view::JS_DEFERED => true]);
        $files = rex_view::getJsFilesWithOptions();
        list($file, $options) = end($files);
        $this->assertTrue($file == 'my_async.js');
        $this->assertTrue($options == [rex_view::JS_ASYNC => true, rex_view::JS_DEFERED => true], 'multiple options');
    }
}
