<?php

namespace Redaxo\Core\Form\Widget;

use Redaxo\Core\Translation\Translatable;

use function in_array;
use function is_array;

/**
 * Shared by the widgets offering a set of choices given as `value => label`, where a nested array is a group of
 * choices, its key being the group label.
 *
 * @internal
 */
trait GroupsChoices
{
    /**
     * The choices split into groups; ungrouped choices form groups without a label.
     *
     * @return list<array{label: string|null, choices: array<int|string, string|Translatable>}>
     */
    public function groups(): array
    {
        $groups = [];
        $ungrouped = [];

        foreach ($this->choices as $value => $label) {
            if (!is_array($label)) {
                $ungrouped[$value] = $label;
                continue;
            }

            if ($ungrouped) {
                $groups[] = ['label' => null, 'choices' => $ungrouped];
                $ungrouped = [];
            }
            $groups[] = ['label' => (string) $value, 'choices' => $label];
        }

        if ($ungrouped) {
            $groups[] = ['label' => null, 'choices' => $ungrouped];
        }

        return $groups;
    }

    public function isSelected(int|string $value): bool
    {
        return in_array((string) $value, (array) $this->selected, true);
    }
}
