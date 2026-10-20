<?php

namespace Redaxo\Core\Translation;

use Override;
use Stringable;

/**
 * A translation key that is resolved lazily, when the text is actually needed.
 *
 * Unlike the result of {@see I18n::msg()} it can be created in constant expressions, so translated texts can be
 * passed to attributes (`#[Text(new Translatable('my_label'))]`). The resolved text is not escaped.
 */
final readonly class Translatable implements Stringable
{
    /** @var list<string|int> */
    public array $replacements;

    public function __construct(
        public string $key,
        string|int ...$replacements,
    ) {
        $this->replacements = array_values($replacements);
    }

    public function translate(?string $locale = null): string
    {
        return null === $locale
            ? I18n::rawMsg($this->key, ...$this->replacements)
            : I18n::rawMsgInLocale($this->key, $locale, ...$this->replacements);
    }

    #[Override]
    public function __toString(): string
    {
        return $this->translate();
    }
}
