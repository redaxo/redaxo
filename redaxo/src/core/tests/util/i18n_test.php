<?php

class rex_i18n_test extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $content = <<<'LANG'
rex_i18n_test_foo = abc def
rex_i18n_test_bar =
rex_i18n_test_baz = ghi
LANG;

        rex_file::put($this->getPath().'/'.rex_i18n::getLocale().'.lang', $content);
    }

    public function tearDown()
    {
        rex_dir::delete($this->getPath());
    }

    private function getPath()
    {
        return rex_path::addonData('tests', 'lang');
    }

    public function testLoadFile()
    {
        rex_i18n::addDirectory($this->getPath());

        $this->assertSame('abc def', rex_i18n::msg('rex_i18n_test_foo'));
        $this->assertSame('', rex_i18n::msg('rex_i18n_test_bar'));
        $this->assertSame('ghi', rex_i18n::msg('rex_i18n_test_baz'));
    }
}
