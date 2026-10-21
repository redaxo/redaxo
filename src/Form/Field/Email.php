<?php

namespace Redaxo\Core\Form\Field;

use Attribute;
use Override;
use Redaxo\Core\Form\InvalidValueException;
use Redaxo\Core\Form\ValueType;
use Redaxo\Core\Translation\Translatable;

use function filter_var;

use const FILTER_FLAG_EMAIL_UNICODE;
use const FILTER_VALIDATE_EMAIL;

/** E-mail address input. */
#[Attribute(Attribute::TARGET_PROPERTY)]
class Email extends Text
{
    #[Override]
    public function fromRaw(string|array|null $raw, ValueType $type): ?string
    {
        $value = parent::fromRaw($raw, $type);

        if (null !== $value && '' !== $value && false === filter_var($value, FILTER_VALIDATE_EMAIL, FILTER_FLAG_EMAIL_UNICODE)) {
            throw new InvalidValueException(new Translatable('form_invalid_email'));
        }

        return $value;
    }

    #[Override]
    protected function inputType(): string
    {
        return 'email';
    }
}
