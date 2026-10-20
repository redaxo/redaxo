<?php

namespace Redaxo\Core\Form;

use Redaxo\Core\Form\Field\Choice;
use Redaxo\Core\Translation\Translatable;

/**
 * A reusable set of choices, e.g. the category tree, usable as {@see Choice::$choices} as well as for standalone
 * widgets.
 *
 * As `new` is allowed in attribute arguments, a provider can be configured right in the field attribute:
 * `#[Choice('Category', choices: new CategoryChoices(ignoreOffline: true))]`.
 */
interface ChoiceProvider
{
    /**
     * The choices as `value => label`; a nested array groups choices, its key being the group label.
     *
     * @return array<int|string, string|Translatable|array<int|string, string|Translatable>>
     */
    public function choices(): array;
}
