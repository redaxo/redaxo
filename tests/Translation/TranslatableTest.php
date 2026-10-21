<?php

namespace Redaxo\Core\Tests\Translation;

use PHPUnit\Framework\TestCase;
use Redaxo\Core\Translation\I18n;
use Redaxo\Core\Translation\Translatable;

/** @internal */
final class TranslatableTest extends TestCase
{
    public function testTranslateAndRender(): void
    {
        I18n::addMsg('translatable_test', 'Hello <b>{0}</b> & <script>x</script>');

        $text = new Translatable('translatable_test', '<i>World</i>');

        self::assertSame('Hello <b><i>World</i></b> & <script>x</script>', $text->translate(), 'the plain text is not escaped');
        self::assertSame(I18n::msg('translatable_test', '<i>World</i>'), (string) $text->render(), 'in HTML it renders like I18n::msg()');
        self::assertStringContainsString('<b>', (string) $text->render());
        self::assertStringNotContainsString('<script>', (string) $text->render());
    }

    public function testText(): void
    {
        I18n::addMsg('translatable_test_text', 'Translated <b>text</b>');

        self::assertSame('Plain <b>text</b>', Translatable::text('Plain <b>text</b>'));
        self::assertSame('Translated <b>text</b>', Translatable::text(new Translatable('translatable_test_text')));
    }
}
