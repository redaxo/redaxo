<?php

namespace Redaxo\Core\Fragment\Component;

use rex_fragment;

class Button extends rex_fragment
{
    private string $fileName = 'core/Component/Button.php';

    public function __construct(
        public string $label,
        public ?string $href = null,
        public ?ButtonTarget $target = null,
        public ?string $prefix = null,
        public ?string $suffix = null,
        public ?ButtonVariant $variant = null,
        public ?ButtonSize $size = null,
        public ?ButtonType $type = null,
        public bool $disabled = false,
        public bool $pill = false,
        public bool $outline = false,
        public bool $caret = false,
        public bool $circle = false,
        public ?string $name = null,
        public ?string $value = null,
        public ?string $slot = null,
        /** @var array<string, string>|null */
        public ?array $attributes = null,
    ) {
        parent::__construct([]);
    }

    public function parse($filename = null): string
    {
        if (!$filename) {
            $filename = $this->fileName;
        }
        return parent::parse($filename);
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
    case parent = '_parent';
    case self = '_self';
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
