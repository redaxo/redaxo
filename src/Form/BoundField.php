<?php

namespace Redaxo\Core\Form;

use Redaxo\Core\Form\Field\Field;
use Redaxo\Core\Translation\Translatable;

/**
 * A field of a concrete form, carrying everything a field type needs to create its widget.
 *
 * Created by {@see Form::field()}.
 */
final readonly class BoundField
{
    /** @internal */
    public function __construct(
        /** Property name. */
        public string $name,
        public Field $field,
        public ValueType $type,
        /** Value of the `name` attribute, e.g. `settings[lang]`. */
        public string $inputName,
        public string $id,
        /** @var string|list<string> */
        public string|array $raw,
        public string|Translatable|null $error = null,
    ) {}
}
