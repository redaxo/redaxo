<?php

namespace Redaxo\Core\Form;

use Redaxo\Core\Backend\Accesskey;
use Redaxo\Core\Translation\Translatable;

/**
 * An additional submit button of a {@see Form}, besides the save button.
 *
 * Which button submitted the form is checked by {@see Form::isSubmittedBy()}, e.g. to stay on the form after saving
 * ("apply"), to trigger an additional action, or to do something else instead of saving.
 */
final readonly class SubmitButton
{
    public function __construct(
        public string $name,
        public string|Translatable $label,
        /** Css class for the button style, e.g. `btn-apply` or `btn-delete`. */
        public string $class = 'btn-apply',
        /** Key of {@see Accesskey::$keys}. */
        public ?string $accesskey = null,
    ) {}

    /** The button for saving without leaving the form. */
    public static function apply(): self
    {
        return new self('apply', new Translatable('form_apply'), accesskey: 'apply');
    }
}
