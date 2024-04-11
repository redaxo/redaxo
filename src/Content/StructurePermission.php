<?php

namespace Redaxo\Core\Content;

use Redaxo\Core\Translation\I18n;
use rex_category_select;
use rex_complex_perm;

use function count;
use function in_array;

class StructurePermission extends rex_complex_perm
{
    /**
     * @param int $categoryId
     *
     * @return bool
     */
    public function hasCategoryPerm($categoryId)
    {
        if ($this->hasAll() || in_array($categoryId, $this->perms)) {
            return true;
        }
        if ($c = Category::get($categoryId)) {
            foreach ($c->getPathAsArray() as $k) {
                if (in_array($k, $this->perms)) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * @return bool
     */
    public function hasStructurePerm()
    {
        return $this->hasAll() || count($this->perms) > 0;
    }

    /**
     * @return array
     */
    public function getMountpoints()
    {
        return $this->hasAll() ? [] : $this->perms;
    }

    /**
     * @return bool
     */
    public function hasMountpoints()
    {
        return !$this->hasAll() && count($this->perms) > 0;
    }

    /**
     * @return list<Category>
     */
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
            $parents[$category->getParentId()] = true;
        }

        if (count($parents) <= 1) {
            usort($categories, static function (Category $a, Category $b) {
                return $a->getPriority() <=> $b->getPriority();
            });
        } else {
            usort($categories, static function (Category $a, Category $b) {
                return strcasecmp($a->getName(), $b->getName());
            });
        }

        return $categories;
    }

    /**
     * @return array
     */
    public static function getFieldParams()
    {
        return [
            'label' => I18n::msg('categories'),
            'all_label' => I18n::msg('all_categories'),
            'select' => new rex_category_select(false, false, false, false),
        ];
    }
}
