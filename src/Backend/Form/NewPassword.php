<?php

namespace Redaxo\Core\Backend\Form;

use Attribute;
use Override;
use Redaxo\Core\Form\BoundField;
use Redaxo\Core\Form\Field\Field;
use Redaxo\Core\Form\FieldRow;
use Redaxo\Core\Form\ValueType;
use Redaxo\Core\Form\Widget\Input;
use Redaxo\Core\Security\BackendLogin;
use Redaxo\Core\Util\Type;
use Redaxo\Core\View\HtmlAttributes;

/**
 * Password input for setting a new backend password, with the client-side rules and the description of the backend
 * password policy.
 *
 * The value is never rendered back into the input. The policy itself has to be checked by the caller, as the check
 * needs the user (it rejects previous passwords).
 *
 * @internal
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
final class NewPassword extends Field
{
    #[Override]
    public function supports(ValueType $type): bool
    {
        return $type->isAnyOf('string');
    }

    #[Override]
    public function toRaw(mixed $value): string
    {
        return '';
    }

    #[Override]
    public function fromRaw(string|array|null $raw, ValueType $type): ?string
    {
        $value = parent::fromRaw($raw, $type);

        return null === $value ? null : Type::string($value);
    }

    #[Override]
    public function widget(BoundField $field): Input
    {
        /** @var array<literal-string, string> $policyAttributes */
        $policyAttributes = BackendLogin::getPasswordPolicy()->getHtmlAttributes();

        return new Input(
            $field->inputName,
            id: $field->id,
            type: 'password',
            required: $this->required,
            attributes: new HtmlAttributes(['autocomplete' => 'new-password'] + $policyAttributes),
        );
    }

    #[Override]
    public function row(BoundField $field): FieldRow
    {
        return new FieldRow(
            $this->widget($field),
            $this->label,
            note: $this->note ?? BackendLogin::getPasswordPolicy()->getDescription(),
            error: $field->error,
            required: $this->required,
        );
    }
}
