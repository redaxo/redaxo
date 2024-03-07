<?php

namespace Redaxo\Core\Tests\Translation;

use PHPUnit\Framework\TestCase;
use Redaxo\Core\Filesystem\Dir;
use Redaxo\Core\Filesystem\File;
use Redaxo\Core\Filesystem\Path;
use Redaxo\Core\Translation\I18n;

class I18nTransCb
{
    public static function mytranslate(): string
    {
        return 'translated';
    }
}

/**
 * @internal
 */
class I18nTest extends TestCase
{
    private string $previousLocale;

    protected function setUp(): void
    {
        $this->previousLocale = I18n::setLocale('de_de', false);

        $content = <<<'LANG'
            rex_i18n_test_foo = abc def
            rex_i18n_test_bar =
            rex_i18n_test_baz = ghi
            rex_i18n_test_4=abc=def

            LANG;
        $content .= "rex_i18n_test_5   =   abc def   \n";

        File::put($this->getPath() . '/de_de.lang', $content . "\nmy=DE");

        $content .= "\nrex_i18n_test_6 = test6\n";

        File::put($this->getPath() . '/en_gb.lang', $content . "\nmy=EN");
    }

    protected function tearDown(): void
    {
        Dir::delete($this->getPath());
        I18n::setLocale($this->previousLocale, false);
    }

    private function getPath(): string
    {
        return Path::addonData('tests', 'lang');
    }

    public function testLoadFile(): void
    {
        I18n::addDirectory($this->getPath());

        self::assertSame('abc def', I18n::msg('rex_i18n_test_foo'));
        self::assertSame('[translate:rex_i18n_test_bar]', I18n::msg('rex_i18n_test_bar'));
        self::assertSame('ghi', I18n::msg('rex_i18n_test_baz'));
        self::assertSame('abc=def', I18n::msg('rex_i18n_test_4'));
        self::assertSame('abc def', I18n::msg('rex_i18n_test_5'));
    }

    public function testHasMsg(): void
    {
        I18n::addDirectory($this->getPath());

        self::assertTrue(I18n::hasMsg('rex_i18n_test_foo'));
        self::assertFalse(I18n::hasMsg('rex_i18n_test_bar'));
        self::assertFalse(I18n::hasMsg('rex_i18n_test_6'));
    }

    public function testHasMsgOrFallback(): void
    {
        I18n::addDirectory($this->getPath());

        self::assertTrue(I18n::hasMsgOrFallback('rex_i18n_test_foo'));
        self::assertFalse(I18n::hasMsgOrFallback('rex_i18n_test_bar'));
        self::assertTrue(I18n::hasMsgOrFallback('rex_i18n_test_6'));
    }

    public function testGetMsgFallback(): void
    {
        I18n::addDirectory($this->getPath());

        self::assertSame('test6', I18n::msg('rex_i18n_test_6'));
        self::assertSame('[translate:rex_i18n_test_7]', I18n::msg('rex_i18n_test_7'));
    }

    public function testGetMsgInLocaleFallback(): void
    {
        I18n::addDirectory($this->getPath());

        self::assertSame('DE', I18n::msgInLocale('my', 'de_de'));
        self::assertSame('EN', I18n::msgInLocale('my', 'en_gb'));
    }

    public function testTranslateCallable(): void
    {
        self::assertSame('translated', I18n::translate('translate:my_cb', false, I18nTransCb::mytranslate(...)));
    }
}
