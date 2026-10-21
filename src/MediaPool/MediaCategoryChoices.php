<?php

namespace Redaxo\Core\MediaPool;

use Override;
use Redaxo\Core\Core;
use Redaxo\Core\Form\ChoiceProvider;
use Redaxo\Core\Translation\Translatable;

use function str_repeat;

/**
 * The media category tree as choices (`id => name`), indented by level.
 *
 * With `checkPerms` only the categories the current user has permission for are offered, so a submitted category
 * without permission is rejected as an invalid choice. As media category permissions are not inherited, the
 * descendants of a category without permission are still offered, moved up a level.
 */
final readonly class MediaCategoryChoices implements ChoiceProvider
{
    /** Value of the "no category" choice. */
    public const int ROOT = 0;

    public function __construct(
        /** Requires a logged-in user. */
        public bool $checkPerms = true,
        /** Whether to offer "no category" as {@see self::ROOT}. */
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
        $perm = $this->checkPerms ? Core::requireUser()->getComplexPerm('media') : null;

        $choices = [];
        if ($this->root && (!$perm || $perm->hasCategoryPerm(null))) {
            $choices[self::ROOT] = new Translatable('pool_kats_no');
        }

        if (null === $this->rootIds) {
            $categories = MediaCategory::getRootCategories();
        } else {
            $categories = [];
            foreach ($this->rootIds as $id) {
                if ($category = MediaCategory::get($id)) {
                    $categories[] = $category;
                }
            }
        }

        foreach ($categories as $category) {
            $this->add($choices, $category, 0, $perm);
        }

        return $choices;
    }

    /** @param array<int, string|Translatable> $choices */
    private function add(array &$choices, MediaCategory $category, int $level, ?MediaPoolPermission $perm): void
    {
        if (!$perm || $perm->hasCategoryPerm($category->id)) {
            $choices[$category->id] = str_repeat("\u{A0}\u{A0}\u{A0}", $level) . $category->name;
            ++$level;
        }

        foreach ($category->getChildren() as $child) {
            $this->add($choices, $child, $level, $perm);
        }
    }
}
