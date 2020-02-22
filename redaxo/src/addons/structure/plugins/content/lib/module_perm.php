<?php

/**
 * @package redaxo\structure\content
 */
class rex_module_perm extends rex_complex_perm
{
    /**
     * @return bool
     */
    public function hasPerm($module_id)
    {
        return $this->hasAll() || in_array($module_id, $this->perms);
    }

    public static function getFieldParams()
    {
        return [
            'label' => rex_i18n::msg('modules'),
            'all_label' => rex_i18n::msg('all_modules'),
            'sql_options' => 'select name, id from ' . rex::getTablePrefix() . 'module order by name',
        ];
    }
}
