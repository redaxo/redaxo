<?php

namespace Redaxo\Core\Tests\Language;

use Override;
use PHPUnit\Framework\TestCase;
use Redaxo\Core\Language\Language;
use Redaxo\Core\Language\LanguageHandler;

/** @internal */
final class LanguageHandlerTest extends TestCase
{
    private ?int $languageId = null;

    #[Override]
    protected function tearDown(): void
    {
        if (null !== $this->languageId) {
            LanguageHandler::delete($this->languageId);
        }
    }

    public function testAddAndEdit(): void
    {
        $count = Language::count();

        $language = LanguageHandler::add(['code' => 'xx', 'name' => 'Test']);
        $this->languageId = $language->id;

        self::assertSame($count + 1, $language->priority);
        self::assertFalse($language->isOnline());

        $language = LanguageHandler::edit($language->id, ['status' => true]);

        self::assertSame('xx', $language->code);
        self::assertSame('Test', $language->name);
        self::assertSame($count + 1, $language->priority);
        self::assertTrue($language->isOnline());
    }
}
