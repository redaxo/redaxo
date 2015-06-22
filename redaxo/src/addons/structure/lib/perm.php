<?php

/**
 * @package redaxo\structure
 */
class rex_structure_perm extends rex_complex_perm
{
    public function hasCategoryPerm($category_id)
    {
        if ($this->hasAll() || in_array($category_id, $this->perms)) {
            return true;
        }
        if ($c = rex_category::get($category_id)) {
            foreach ($c->getPathAsArray() as $k) {
                if (in_array($k, $this->perms)) {
                    return true;
                }
            }
        }
        return false;
    }

    public function hasStructurePerm()
    {
        return $this->hasAll() || count($this->perms) > 0;
    }

    public function getMountpoints()
    {
        return $this->hasAll() ? null : $this->perms;
    }

    public function hasMountpoints()
    {
        return !$this->hasAll() && count($this->perms) > 0;
    }

    public static function getFieldParams()
    {
        return [
            'label' => rex_i18n::msg('categories'),
            'all_label' => rex_i18n::msg('all_categories'),
            'select' => new rex_category_select(false, false, false, false),
        ];
    }
}
