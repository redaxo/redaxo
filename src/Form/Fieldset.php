<?php

namespace Redaxo\Core\Form;

use Attribute;
use Redaxo\Core\Translation\Translatable;

/**
 * Starts a group of fields: the annotated property and all following ones, up to the next fieldset.
 *
 * Only affects the default rendering ({@see Form::rest()}), custom markup arranges the fields itself.
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
final readonly class Fieldset
{
    public function __construct(
        public string|Translatable|null $legend = null,
    ) {}
}
