<?php

class rex_path_test extends PHPUnit_Framework_TestCase
{
    public function testAbsoluteConversion()
    {
        $path = rex_path::absolute('c:/abc/../def/./xy');
        $this->assertEquals($this->path('c:/def/xy'), $path, 'resolves .. and .');

        $path = rex_path::absolute('c:\abc\..\def\.\xy');
        $this->assertEquals($this->path('c:\def\xy'), $path, 'resolves .. and .');
    }

    public function testBasename()
    {
        $this->assertSame('config.yml', rex_path::basename('../redaxo/data/core/config.yml'));

        $this->assertSame('config.yml', rex_path::basename('..\redaxo\data\core\config.yml'));
    }

    private function path($path)
    {
        return str_replace(['\\', '/'], DIRECTORY_SEPARATOR, $path);
    }
}
