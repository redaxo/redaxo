<?php

/**
 * @package redaxo\mediapool
 */
class rex_media_read_perm extends rex_media_perm
{
    /**
     * @return array
     */
    public static function getFieldParams()
    {
        return [
            'label' => rex_i18n::msg('mediafolder_read'),
            'all_label' => rex_i18n::msg('all_mediafolder_read'),
            'select' => new rex_media_category_select(false, false, true),
        ];
    }
}
