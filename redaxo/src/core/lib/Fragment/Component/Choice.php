<?php

namespace Redaxo\Core\Fragment\Component;

use Closure;
use Redaxo\Core\Fragment\Attributes;
use Redaxo\Core\Fragment\Fragment;

use rex_type;

use function in_array;
use function is_array;
use function is_int;

/**
 * @see redaxo/src/core/fragments/core/Component/Choice.php
 * @see redaxo/src/core/fragments/core/Component/ChoiceCheckbox.php
 * @see redaxo/src/core/fragments/core/Component/ChoiceRadio.php
 * @see redaxo/src/core/fragments/core/Component/ChoiceSelect.php
 */
class Choice extends Fragment
{
    /** @var array<string|int, array<string, string|int>> */
    private array $groupedChoices = [];

    /** @var list<string|int> */
    private array $allValues = [];

    /** @var list<string|int> */
    private array $selectedValues = [];

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
         * @var null|string|int|list<string|int>
         */
        public null|string|int|array $value = null,

        /**
         * Choices is an array, where the array key is the
         * item's label and the array value is the item's
         * value.
         *
         * @var array<string|int, string|int|array<string, string|int>>
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

        public Attributes $attributes = new Attributes(),

        /**
         * By default, the array key of each item in the
         * choices option is used as the text that's shown
         * to the user. The choiceLabel option allows you
         * to take more control.
         *
         * @var Closure(string|int,string):string|null
         */
        public null|Closure $choiceLabel = null,
    ) {}

    public function render(): string
    {
        $currentGroup = [];
        foreach ($this->choices as $choiceLabel => $choiceValue) {
            if (!is_array($choiceValue)) {
                $choiceLabel = rex_type::string($choiceLabel);

                if ($this->choiceLabel) {
                    $choiceLabel = ($this->choiceLabel)($choiceValue, $choiceLabel);
                }

                $currentGroup[$choiceLabel] = $choiceValue;
                $this->allValues[] = $choiceValue;

                continue;
            }

            if ($currentGroup) {
                $this->groupedChoices[] = $currentGroup;
                $currentGroup = [];
            }

            foreach ($choiceValue as $nestedLabel => $nestedValue) {
                if ($this->choiceLabel) {
                    $nestedLabel = ($this->choiceLabel)($nestedValue, $nestedLabel);
                }

                $this->groupedChoices[$choiceLabel][$nestedLabel] = $nestedValue;
                $this->allValues[] = $nestedValue;
            }
        }
        if ($currentGroup) {
            $this->groupedChoices[] = $currentGroup;
        }

        if (null === $this->value) {
            $this->selectedValues = [];
        } else {
            $this->selectedValues = is_array($this->value) ? $this->value : [$this->value];
        }

        // verify given values
        $this->selectedValues = array_values(array_filter($this->selectedValues, function (string|int $value) {
            return in_array($value, $this->allValues, true);
        }));

        if ($this->multiple && $this->name) {
            $this->name .= '[]';
        }

        return parent::render();
    }

    protected function getPath(): string
    {
        return 'core/Component/Choice.php';
    }

    /** @return list<string|int> */
    public function getValues(): array
    {
        return $this->selectedValues;
    }

    /**
     * @return iterable<string|null, array<string, string|int>>
     */
    public function getChoices(): iterable
    {
        foreach ($this->groupedChoices as $label => $group) {
            yield is_int($label) ? null : $label => $group;
        }
    }
}

enum ChoiceType
{
    // case Button;
    case Check;
    case Select;
}
