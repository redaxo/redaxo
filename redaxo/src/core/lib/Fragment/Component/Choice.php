<?php

namespace Redaxo\Core\Fragment\Component;

use Closure;
use Redaxo\Core\Fragment\Fragment;

use function call_user_func;
use function is_array;
use function is_callable;

/**
 * @see redaxo/src/core/fragments/core/Component/Choice.php
 * @see redaxo/src/core/fragments/core/Component/ChoiceCheckbox.php
 * @see redaxo/src/core/fragments/core/Component/ChoiceRadio.php
 * @see redaxo/src/core/fragments/core/Component/ChoiceSelect.php
 */
class Choice extends Fragment
{
    /** @var array<string, array<string, string>|string> */
    private array $choicesByLabels = [];

    /** @var array<string, string> */
    private array $choicesByValues = [];

    public function __construct(
        /**
         * The choice label.
         */
        public string|Fragment|null $label = null,

        /**
         * Text that describes how to use the choice.
         */
        public string|Fragment|null $notice = null,

        /**
         * The name of the choice.
         */
        public ?string $name = null,

        /**
         * The current values of the choice.
         *
         * @var null|string|array<string>
         */
        public null|string|array $value = null,

        /**
         * Choices is an array, where the array key is the
         * item's label and the array value is the item's
         * value.
         *
         * @var array<string, array<string, string>|string>
         */
        public array $choices = [],

        /**
         * Disables the choice.
         */
        public bool $disabled = false,

        /**
         * Allows more than one option to be selected.
         */
        public bool $multiple = false,

        /**
         * Placeholder text to show as a hint when the choice
         * is empty.
         */
        public ?string $placeholder = null,

        /**
         * Makes the choice a required field.
         */
        public bool $required = false,

        /**
         * The choice type.
         */
        public ChoiceType $type = ChoiceType::Select,

        /** @var array<string, string|int> */
        public array $attributes = [],

        /**
         * By default, the array key of each item in the
         * choices option is used as the text that's shown
         * to the user. The choiceLabel option allows you
         * to take more control.
         */
        public null|string|Closure $choiceLabel = null,
    ) {
    }

    public function render(): string
    {
        foreach ($this->choices as $choiceLabel => $choiceValue) {
            if (!is_array($choiceValue)) {
                if (is_callable($this->choiceLabel)) {
                    $choiceLabel = (string) call_user_func($this->choiceLabel, $choiceValue, $choiceLabel);
                }

                $this->choicesByLabels[trim($choiceLabel)] = trim($choiceValue);
                $this->choicesByValues[trim($choiceValue)] = trim($choiceLabel);
                continue;
            }
            foreach ($choiceValue as $nestedLabel => $nestedValue) {
                if (is_callable($this->choiceLabel)) {
                    $nestedLabel = (string) call_user_func($this->choiceLabel, $nestedValue, $nestedLabel);
                }
                $this->choicesByLabels[trim($choiceLabel)][trim($nestedLabel)] = trim($nestedValue);
                $this->choicesByValues[trim($nestedValue)] = trim($nestedLabel);
            }
        }

        if (null === $this->value) {
            $this->value = [];
        } elseif (!is_array($this->value)) {
            $this->value = [$this->value];
        }

        // verify given values
        foreach ($this->value as $index => $value) {
            if (!isset($this->choicesByValues[trim($value)])) {
                unset($this->value[$index]);
            }
        }

        if ($this->multiple && $this->name) {
            $this->name .= '[]';
        }

        return parent::render();
    }

    protected function getPath(): string
    {
        return 'core/Component/Choice.php';
    }

    /**
     * @return array<string, array<string, string>|string>
     */
    public function getChoices(): array
    {
        return $this->choicesByLabels;
    }
}

enum ChoiceType
{
    // case Button;
    case Check;
    case Select;
}
