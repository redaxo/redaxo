<?php

namespace Redaxo\Core\Fragment\Component;

use Redaxo\Core\Fragment\Enum\FormControl\Autocapitalize;
use Redaxo\Core\Fragment\Slot;
use rex_fragment;
use rex_functional_exception;

class Textarea extends rex_fragment
{
    private string $fileName = 'core/Component/Textarea.php';

    public function __construct(
        /**
         * The textarea's label. Alternatively, you can use
         * the label attribute.
         */
        public ?Slot $slotLabel = null,

        /**
         * Text that describes how to use the textarea.
         * Alternatively, you can use the notice property.
         */
        public ?Slot $slotNotice = null,

        /**
         * The textarea's label. If you need to display HTML,
         * use the label slot instead.
         */
        public ?string $label = null,

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
         * The textarea's notice. If you need to display
         * HTML, use the notice slot instead.
         */
        public ?string $notice = null,

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

        /** @var array<string, string>|null */
        public ?array $attributes = null,
    ) {

        parent::__construct([]);
    }

    public function render(): string
    {
        return parent::parse($this->fileName);
    }
}

enum TextareaResize: string
{
    case Auto = 'auto';
    case None = 'none';
    case Vertical = 'vertical';
}

