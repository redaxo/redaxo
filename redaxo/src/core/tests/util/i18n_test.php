<?php

class rex_i18n_test extends PHPUnit_Framework_TestCase
{
    private $previousLocale;

    public function setUp()
    {
        $this->previousLocale = rex_i18n::setLocale('de_de', false);

        $content = <<<'LANG'
rex_i18n_test_foo = abc def
rex_i18n_test_bar =
rex_i18n_test_baz = ghi
rex_i18n_test_4=abc=def

LANG;
        $content .= "rex_i18n_test_5   =   abc def   \n";

        rex_file::put($this->getPath().'/de_de.lang', $content);

        $content .= "\nrex_i18n_test_6 = test6\n";

        rex_file::put($this->getPath().'/en_gb.lang', $content);
    }

    public function tearDown()
    {
        rex_dir::delete($this->getPath());
        rex_i18n::setLocale($this->previousLocale, false);
    }

    private function getPath()
    {
        return rex_path::addonData('tests', 'lang');
    }

    public function testLoadFile()
    {
        rex_i18n::addDirectory($this->getPath());

        $this->assertSame('abc def', rex_i18n::msg('rex_i18n_test_foo'));
        $this->assertSame('[translate:rex_i18n_test_bar]', rex_i18n::msg('rex_i18n_test_bar'));
        $this->assertSame('ghi', rex_i18n::msg('rex_i18n_test_baz'));
        $this->assertSame('abc=def', rex_i18n::msg('rex_i18n_test_4'));
        $this->assertSame('abc def', rex_i18n::msg('rex_i18n_test_5'));
    }

    public function testHasMsg()
    {
        rex_i18n::addDirectory($this->getPath());

        $this->assertTrue(rex_i18n::hasMsg('rex_i18n_test_foo'));
        $this->assertFalse(rex_i18n::hasMsg('rex_i18n_test_bar'));
        $this->assertFalse(rex_i18n::hasMsg('rex_i18n_test_6'));
    }

    public function testHasMsgOrFallback()
    {
        rex_i18n::addDirectory($this->getPath());

        $this->assertTrue(rex_i18n::hasMsgOrFallback('rex_i18n_test_foo'));
        $this->assertFalse(rex_i18n::hasMsgOrFallback('rex_i18n_test_bar'));
        $this->assertTrue(rex_i18n::hasMsgOrFallback('rex_i18n_test_6'));
    }

    public function testGetMsgFallback()
    {
        rex_i18n::addDirectory($this->getPath());

        $this->assertSame('test6', rex_i18n::msg('rex_i18n_test_6'));
        $this->assertSame('[translate:rex_i18n_test_7]', rex_i18n::msg('rex_i18n_test_7'));
    }
}
