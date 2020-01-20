<?php

/**
 * @package redaxo\mediapool
 */
class rex_media_perm extends rex_complex_perm
{
    public function hasCategoryPerm($category_id)
    {
        return $this->hasAll() || in_array($category_id, $this->perms);
    }

    public function hasMediaPerm()
    {
        return $this->hasAll() || count($this->perms) > 0;
    }

    public function getFirstId()
    {
        if (is_array($this->perms) && count($this->perms) > 0) {
            return $this->perms[0];
        }
        return null;
    }

    public static function getFieldParams()
    {
        return [
            'label' => rex_i18n::msg('mediafolder'),
            'all_label' => rex_i18n::msg('all_mediafolder'),
            'select' => new rex_media_category_select(false),
        ];
    }
}

/**
 * @package redaxo\mediapool
 */
class rex_media_read_perm extends rex_media_perm
{
    public static function getFieldParams()
    {
        return [
            'label' => rex_i18n::msg('mediafolder_read'),
            'all_label' => rex_i18n::msg('all_mediafolder_read'),
            'select' => new rex_media_category_select(false),
        ];
    }
}
