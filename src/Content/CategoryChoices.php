<?php

namespace Redaxo\Core\Content;

use Override;
use Redaxo\Core\Core;
use Redaxo\Core\Form\ChoiceProvider;
use Redaxo\Core\Translation\Translatable;

use function str_repeat;

/**
 * The category tree as choices (`id => name`), indented by level.
 *
 * With `checkPerms` only the categories the current user has permission for are offered, so a submitted category
 * without permission is rejected as an invalid choice.
 */
final readonly class CategoryChoices implements ChoiceProvider
{
    /** Value of the root level choice. */
    public const int ROOT = 0;

    public function __construct(
        /** Language of the category names; defaults to the current language. */
        public ?int $languageId = null,
        public bool $ignoreOffline = false,
        /** Requires a logged-in user. */
        public bool $checkPerms = true,
        /** Whether to offer the root level as {@see self::ROOT}, e.g. as target for moving articles. */
        public bool $root = false,
        /**
         * Restricts the tree to these categories and their descendants; `null` for the whole tree.
         *
         * @var list<int>|null
         */
        public ?array $rootIds = null,
    ) {}

    /** @return array<int, string|Translatable> */
    #[Override]
    public function choices(): array
    {
        $perm = $this->checkPerms ? Core::requireUser()->getComplexPerm('structure') : null;

        $choices = [];
        if ($this->root && null === $this->rootIds && (!$perm || $perm->hasCategoryPerm(null))) {
            $choices[self::ROOT] = new Translatable('root_level');
        }

        foreach ($this->rootCategories($perm) as $category) {
            $this->add($choices, $category, 0, $perm);
        }

        return $choices;
    }

    /** @return list<Category> */
    private function rootCategories(?StructurePermission $perm): array
    {
        if (null !== $this->rootIds) {
            $categories = [];
            foreach ($this->rootIds as $id) {
                if ($category = Category::get($id, $this->languageId)) {
                    $categories[] = $category;
                }
            }

            return $categories;
        }

        if (!$perm || $perm->hasCategoryPerm(null)) {
            return Category::getRootCategories($this->ignoreOffline, $this->languageId);
        }

        // users restricted to mount points get them as roots
        $categories = [];
        foreach ($perm->getMountpointCategories() as $mountpoint) {
            $category = Category::get($mountpoint->id, $this->languageId);
            if ($category && (!$this->ignoreOffline || $category->isOnline())) {
                $categories[] = $category;
            }
        }

        return $categories;
    }

    /** @param array<int, string|Translatable> $choices */
    private function add(array &$choices, Category $category, int $level, ?StructurePermission $perm): void
    {
        if ($perm && !$perm->hasCategoryPerm($category->id)) {
            return;
        }

        $choices[$category->id] = str_repeat("\u{A0}\u{A0}\u{A0}", $level) . $category->name . ' [' . $category->id . ']';

        foreach ($category->getChildren($this->ignoreOffline) as $child) {
            $this->add($choices, $child, $level + 1, $perm);
        }
    }
}
