<?php

namespace Redaxo\Core\Backend\Form;

use Redaxo\Core\Form\Widget\Widget;
use Redaxo\Core\Translation\Translatable;
use Redaxo\Core\View\Renderable;
use Redaxo\Core\View\RendersView;

use function preg_replace;

/**
 * The rows of the {@see Permissions} field: a multi-select per permission, plus an "all" checkbox for complex
 * permissions.
 *
 * @internal
 */
final class PermissionsInput implements Widget
{
    use RendersView;

    public function __construct(
        /** The `[]` suffix is appended automatically. */
        public string $name,
        public string $id,
        /**
         * Choice keys and selected values are prefixed with the permission key (`key:item`).
         *
         * @var list<array{key: string, label: string|Renderable, allLabel: string|Renderable|null, choices: array<string, string|Translatable|array<string, string|Translatable>>, selected: list<string>, all: bool}>
         */
        public array $groups,
        public string|Renderable|null $error = null,
    ) {}

    public function groupId(string $key): string
    {
        return $this->id . '-' . (string) preg_replace('/[^\w-]/', '_', $key);
    }

    public function labelTarget(): ?string
    {
        return null;
    }
}
