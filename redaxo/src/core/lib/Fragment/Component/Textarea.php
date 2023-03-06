<?php

namespace Redaxo\Core\Fragment\Component;

use Redaxo\Core\Fragment\Enum\FormControl\Autocapitalize;
use Redaxo\Core\Fragment\Fragment;

class Textarea extends Fragment
{
    public function __construct(
        /**
         * The textarea's label.
         */
        public string|Fragment|null $label = null,

        /**
         * Text that describes how to use the textarea.
         */
        public string|Fragment|null $notice = null,

        /**
         * The name of the textarea, submitted as a
         * name/value pair with form data.
         */
        public ?string $name = null,

        /**
         * The current value of the textarea, submitted as a
         * name/value pair with form data.
         */
        public ?string $value = null,

        /**
         * Disables the textarea.
         */
        public bool $disabled = false,

        /**
         * Placeholder text to show as a hint when the
         * textarea is empty.
         */
        public ?string $placeholder = null,

        /**
         * The number of rows to display by default.
         */
        public int $rows = 4,

        /**
         * Controls how the textarea can be resized.
         */
        public TextareaResize $resize = TextareaResize::Vertical,

        /**
         * Makes the textarea readonly.
         */
        public bool $readonly = false,

        /**
         * Makes the textarea a required field.
         */
        public bool $required = false,

        /**
         * The minimum length of textarea that will be
         * considered valid.
         */
        public ?int $minlength = null,

        /**
         * The maximum length of textarea that will be
         * considered valid.
         */
        public ?int $maxlength = null,

        /**
         * Indicates that the textarea should receive focus
         * on page load.
         */
        public bool $autofocus = false,

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

        /** @var array<string, string|int> */
        public array $attributes = [],
    ) {}

    protected function getPath(): string
    {
        return 'core/Component/Textarea.php';
    }
}

enum TextareaResize: string
{
    case Auto = 'auto';
    case None = 'none';
    case Vertical = 'vertical';
}
