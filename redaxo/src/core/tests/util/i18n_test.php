<?php

use PHPUnit\Framework\TestCase;

class rex_i18n_trans_cb
{
    public static function mytranslate()
    {
        return 'translated';
    }
}

/**
 * @internal
 */
class rex_i18n_test extends TestCase
{
    private $previousLocale;

    protected function setUp()
    {
        $this->previousLocale = rex_i18n::setLocale('de_de', false);

        $content = <<<'LANG'
rex_i18n_test_foo = abc def
rex_i18n_test_bar =
rex_i18n_test_baz = ghi
rex_i18n_test_4=abc=def

LANG;
        $content .= "rex_i18n_test_5   =   abc def   \n";

        rex_file::put($this->getPath().'/de_de.lang', $content ."\nmy=DE");

        $content .= "\nrex_i18n_test_6 = test6\n";

        rex_file::put($this->getPath().'/en_gb.lang', $content."\nmy=EN");
    }

    protected function tearDown()
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

        static::assertSame('abc def', rex_i18n::msg('rex_i18n_test_foo'));
        static::assertSame('[translate:rex_i18n_test_bar]', rex_i18n::msg('rex_i18n_test_bar'));
        static::assertSame('ghi', rex_i18n::msg('rex_i18n_test_baz'));
        static::assertSame('abc=def', rex_i18n::msg('rex_i18n_test_4'));
        static::assertSame('abc def', rex_i18n::msg('rex_i18n_test_5'));
    }

    public function testHasMsg()
    {
        rex_i18n::addDirectory($this->getPath());

        static::assertTrue(rex_i18n::hasMsg('rex_i18n_test_foo'));
        static::assertFalse(rex_i18n::hasMsg('rex_i18n_test_bar'));
        static::assertFalse(rex_i18n::hasMsg('rex_i18n_test_6'));
    }

    public function testHasMsgOrFallback()
    {
        rex_i18n::addDirectory($this->getPath());

        static::assertTrue(rex_i18n::hasMsgOrFallback('rex_i18n_test_foo'));
        static::assertFalse(rex_i18n::hasMsgOrFallback('rex_i18n_test_bar'));
        static::assertTrue(rex_i18n::hasMsgOrFallback('rex_i18n_test_6'));
    }

    public function testGetMsgFallback()
    {
        rex_i18n::addDirectory($this->getPath());

        static::assertSame('test6', rex_i18n::msg('rex_i18n_test_6'));
        static::assertSame('[translate:rex_i18n_test_7]', rex_i18n::msg('rex_i18n_test_7'));
    }

    public function testGetMsgInLocaleFallback()
    {
        rex_i18n::addDirectory($this->getPath());

        static::assertSame('DE', rex_i18n::msgInLocale('my', 'de_de'));
        static::assertSame('EN', rex_i18n::msgInLocale('my', 'en_gb'));
    }

    public function testTranslateCallable()
    {
        static::assertSame('translated', rex_i18n::translate('translate:my_cb', false, ['rex_i18n_trans_cb', 'mytranslate']));
    }
}
