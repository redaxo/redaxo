<?php

namespace Redaxo\Core\Form\Field;

use Attribute;
use Override;
use Redaxo\Core\Form\BoundField;
use Redaxo\Core\Form\InvalidValueException;
use Redaxo\Core\Form\ValueType;
use Redaxo\Core\Form\Widget\Textarea as TextareaWidget;
use Redaxo\Core\Translation\Translatable;
use Redaxo\Core\Util\Type;
use Redaxo\Core\View\HtmlAttributes;
use Redaxo\Core\View\Renderable;

use function mb_strlen;
use function str_replace;

/** Multi-line text input. */
#[Attribute(Attribute::TARGET_PROPERTY)]
class Textarea extends Field
{
    public function __construct(
        string|Renderable $label,
        string|Renderable|null $note = null,
        bool $required = false,
        ?string $key = null,
        /** @var positive-int */
        public readonly int $rows = 5,
        /** @var positive-int|null */
        public readonly ?int $maxLength = null,
    ) {
        parent::__construct($label, $note, $required, $key);
    }

    #[Override]
    public function supports(ValueType $type): bool
    {
        return $type->isAnyOf('string');
    }

    #[Override]
    public function fromRaw(string|array|null $raw, ValueType $type): ?string
    {
        $value = parent::fromRaw($raw, $type);
        $value = null === $value ? null : Type::string($value);

        // browsers count a line break as one character, but submit it as CRLF
        if (null !== $value && null !== $this->maxLength && mb_strlen(str_replace("\r\n", "\n", $value)) > $this->maxLength) {
            throw new InvalidValueException(new Translatable('form_max_length', $this->maxLength));
        }

        return $value;
    }

    #[Override]
    public function widget(BoundField $field): TextareaWidget
    {
        return new TextareaWidget($field->inputName, Type::string($field->raw), $field->id, $this->rows, $this->required, new HtmlAttributes(['maxlength' => $this->maxLength]));
    }
}
