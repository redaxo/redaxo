<?php

namespace Redaxo\Core\Fragment\Component;

use Redaxo\Core\Fragment\Fragment;
use Redaxo\Core\Fragment\HtmlAttributes;

/**
 * @see redaxo/src/core/fragments/core/Component/Button.php
 */
class Button extends Fragment
{
    public function __construct(
        /**
         * The button's label.
         */
        public string|Fragment $label,

        /**
         * A presentational prefix icon or similar element.
         */
        public string|Fragment|null $prefix = null,

        /**
         * A presentational suffix icon or similar element.
         */
        public string|Fragment|null $suffix = null,

        /**
         * When set, the underlying button will be rendered
         * as an <a> with this href instead of a <button>.
         */
        public ?string $href = null,

        /**
         * Tells the browser where to open the link. Only
         * used when href is present.
         */
        public ?ButtonTarget $target = null,

        /**
         * The button's theme variant.
         */
        public ?ButtonVariant $variant = null,

        /**
         * The button's size.
         */
        public ?ButtonSize $size = null,

        /**
         * The type of button. Note that the default value
         * is button instead of submit, which is opposite
         * of how native <button> elements behave. When the
         * type is submit, the button will submit the
         * surrounding form.
         */
        public ?ButtonType $type = null,

        /**
         * Disables the button.
         */
        public bool $disabled = false,

        /**
         * Draws a pill-style button with rounded edges.
         */
        public bool $pill = false,

        /**
         * Draws an outlined button.
         */
        public bool $outline = false,

        /**
         * Draws the button with a caret. Used to indicate
         * that the button triggers a dropdown menu or
         * similar behavior.
         */
        public bool $caret = false,

        /**
         * Draws a circular icon button. When this attribute
         * is present, the button expects a single icon in
         * the default slot.
         */
        public bool $circle = false,

        /**
         * The name of the button, submitted as a
         * name/value pair with form data, but only when
         * this button is the submitter. This attribute is
         * ignored when href is present.
         */
        public ?string $name = null,

        /**
         * The value of the button, submitted as a pair with
         * the button's name as part of the form data, but
         * only when this button is the submitter. This
         * attribute is ignored when href is present.
         */
        public ?string $value = null,

        public HtmlAttributes $attributes = new HtmlAttributes(),
    ) {}

    protected function getPath(): string
    {
        return 'core/Component/Button.php';
    }
}

enum ButtonSize: string
{
    case Small = 'small';
    case Medium = 'medium';
    case Large = 'large';
}

enum ButtonTarget: string
{
    case Blank = '_blank';
    case Parent = '_parent';
    case Self = '_self';
    case Top = '_top';
}

enum ButtonType: string
{
    case Button = 'button';
    case Submit = 'submit';
    case Reset = 'reset';
}

enum ButtonVariant: string
{
    case Default = 'default';
    case Primary = 'primary';
    case Neutral = 'neutral';
    case Success = 'success';
    case Warning = 'warning';
    case Danger = 'danger';
    case Text = 'text';
}
