<?php

namespace Redaxo\Core\Content;

use Override;
use Redaxo\Core\Security\ComplexPermission;
use Redaxo\Core\Security\PermissionChoices;
use Redaxo\Core\Translation\Translatable;

use function count;
use function in_array;

/** @extends ComplexPermission<int> */
final class StructurePermission extends ComplexPermission
{
    /** @param int|null $categoryId `null` for the root level */
    public function hasCategoryPerm(?int $categoryId): bool
    {
        if ($this->hasAll()) {
            return true;
        }
        if (in_array($categoryId ?? 0, $this->perms, true)) {
            return true;
        }
        if (null !== $categoryId && $c = Category::get($categoryId)) {
            $perms = $this->perms;
            return array_any($c->path, static fn (int $k) => in_array($k, $perms, true));
        }
        return false;
    }

    public function hasStructurePerm(): bool
    {
        return $this->hasAll() || count($this->perms) > 0;
    }

    /** @return list<int> */
    public function getMountpoints(): array
    {
        return $this->hasAll() ? [] : $this->perms;
    }

    public function hasMountpoints(): bool
    {
        return !$this->hasAll() && count($this->perms) > 0;
    }

    /** @return list<Category> */
    public function getMountpointCategories(): array
    {
        if ($this->hasAll()) {
            return [];
        }

        $categories = [];
        $parents = [];
        foreach ($this->perms as $id) {
            $category = Category::get($id);
            if (!$category) {
                continue;
            }

            $categories[] = $category;
            $parents[$category->parentId ?? 0] = true;
        }

        if (count($parents) <= 1) {
            usort($categories, static function (Category $a, Category $b) {
                return $a->priority <=> $b->priority;
            });
        } else {
            usort($categories, static function (Category $a, Category $b) {
                return strcasecmp($a->name, $b->name);
            });
        }

        return $categories;
    }

    #[Override]
    public static function getChoices(): PermissionChoices
    {
        return new PermissionChoices(
            new Translatable('categories'),
            new Translatable('all_categories'),
            new CategoryChoices(checkPerms: false),
        );
    }
}
