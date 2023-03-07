<?php

namespace Redaxo\Core\Fragment\Component;

use Redaxo\Core\Fragment\Enum\FormControl\Autocapitalize;
use Redaxo\Core\Fragment\Fragment;
use rex_functional_exception;

use function in_array;

class Input extends Fragment
{
    public function __construct(
        /**
         * The input's label.
         */
        public string|Fragment|null $label = null,

        /**
         * Text that describes how to use the input.
         */
        public string|Fragment|null $notice = null,

        /**
         * Used to prepend a presentational icon or similar
         * element to the input.
         */
        public string|Fragment|null $prefix = null,

        /**
         * A presentational suffix icon or similar element.
         */
        public string|Fragment|null $suffix = null,

        /**
         * The type of input. Works the same as a native
         * <input> element, but only a subset of types are
         * supported. Defaults to text.
         */
        public InputType $type = InputType::Text,

        /**
         * The name of the input, submitted as a name/value
         * pair with form data.
         */
        public ?string $name = null,

        /**
         * The current value of the input, submitted as a
         * name/value pair with form data.
         */
        public ?string $value = null,

        /**
         * Disables the input.
         */
        public bool $disabled = false,

        /**
         * Placeholder text to show as a hint when the input
         * is empty.
         */
        public ?string $placeholder = null,

        /**
         * Makes the input readonly.
         */
        public bool $readonly = false,

        /**
         * Makes the input a required field.
         */
        public bool $required = false,

        /**
         * A regular expression pattern to validate input
         * against.
         */
        public ?string $pattern = null,

        /**
         * The minimum length of input that will be
         * considered valid.
         */
        public ?int $minlength = null,

        /**
         * The maximum length of input that will be
         * considered valid.
         */
        public ?int $maxlength = null,

        /**
         * The input's minimum value. Only applies to date
         * and number input types.
         */
        public null|int|string $min = null,

        /**
         * The input's maximum value. Only applies to date
         * and number input types.
         */
        public null|int|string $max = null,

        /**
         * Specifies the granularity that the value must
         * adhere to, or the special value any which means
         * no stepping is implied, allowing any numeric
         * value. Only applies to date and number input
         * types.
         *
         * @TODO fix psalm notation
         * string = 'any'
         */
        public null|int|float|string $step = null,

        /**
         * Controls whether and how text input is
         * automatically capitalized as it is entered by
         * the user.
         */
        public ?Autocapitalize $autocapitalize = null,

        /**
         * Enables spell checking on the textarea.
         */
        public bool $spellcheck = true,

        /**
         * Indicates that the input should receive focus on
         * page load.
         */
        public bool $autofocus = false,

        /** @var array<string, string|int> */
        public array $attributes = [],
    ) {
        if ($this->min && !in_array($this->type, $this->minMaxTypes())) {
            throw new rex_functional_exception('The min property applies to date and number input types. The current type is '.$this->type->name.'.');
        }
        if ($this->max && !in_array($this->type, $this->minMaxTypes())) {
            throw new rex_functional_exception('The max property applies to date and number input types. The current type is '.$this->type->name.'.');
        }
        if ($this->step && !in_array($this->type, $this->stepTypes())) {
            throw new rex_functional_exception('The step property applies to date and number input types. The current type is '.$this->type->name.'.');
        }
    }

    protected function getPath(): string
    {
        return 'core/Component/Input.php';
    }

    /**
     * @return list<InputType>
     */
    private function minMaxTypes(): array
    {
        return [
            InputType::Date,
            InputType::Number,
        ];
    }

    /**
     * @return list<InputType>
     */
    private function stepTypes(): array
    {
        return [
            InputType::Date,
            InputType::Number,
        ];
    }
}

enum InputType: string
{
    case Date = 'date';
    case DatetimeLocale = 'datetime-local';
    case Email = 'email';
    case Number = 'number';
    case Password = 'password';
    case Search = 'search';
    case Telephone = 'tel';
    case Text = 'text';
    case Time = 'time';
    case Url = 'url';
}
