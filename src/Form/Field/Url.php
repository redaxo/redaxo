<?php

namespace Redaxo\Core\Form\Field;

use Attribute;
use Override;
use Redaxo\Core\Form\InvalidValueException;
use Redaxo\Core\Form\ValueType;
use Redaxo\Core\Translation\Translatable;
use Redaxo\Core\View\Renderable;

use function filter_var;
use function implode;
use function in_array;
use function is_string;
use function parse_url;
use function strtolower;

use const FILTER_VALIDATE_URL;
use const PHP_URL_SCHEME;

/**
 * Absolute URL input.
 *
 * Only the given schemes are accepted, by default `http` and `https`: a URL with e.g. `javascript:` must never be
 * accepted, as such values typically end up in links.
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
class Url extends Text
{
    /**
     * @param int<1, 65535>|null $maxLength
     * @param non-empty-list<lowercase-string> $schemes
     */
    public function __construct(
        string|Renderable $label,
        string|Renderable|null $note = null,
        bool $required = false,
        ?string $key = null,
        ?int $maxLength = null,
        public readonly array $schemes = ['http', 'https'],
    ) {
        parent::__construct($label, $note, $required, $key, $maxLength);
    }

    #[Override]
    public function fromRaw(string|array|null $raw, ValueType $type): ?string
    {
        $value = parent::fromRaw($raw, $type);

        if (null === $value || '' === $value) {
            return $value;
        }

        $scheme = parse_url($value, PHP_URL_SCHEME);
        if (false === filter_var($value, FILTER_VALIDATE_URL) || !is_string($scheme) || !in_array(strtolower($scheme), $this->schemes, true)) {
            throw new InvalidValueException(new Translatable('form_invalid_url', implode(', ', $this->schemes)));
        }

        return $value;
    }

    #[Override]
    protected function inputType(): string
    {
        return 'url';
    }
}
