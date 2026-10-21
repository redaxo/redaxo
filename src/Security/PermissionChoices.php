<?php

namespace Redaxo\Core\Security;

use Redaxo\Core\Form\ChoiceProvider;
use Redaxo\Core\Translation\Translatable;
use Redaxo\Core\View\Renderable;

/** The items of a {@see ComplexPermission} as offered in the role form. */
final readonly class PermissionChoices
{
    public function __construct(
        public string|Renderable $label,
        /** Label of the checkbox granting the permission for all items, including future ones. */
        public string|Renderable $allLabel,
        /** @var array<int|string, string|Translatable|array<int|string, string|Translatable>>|ChoiceProvider */
        public array|ChoiceProvider $choices,
    ) {}
}
