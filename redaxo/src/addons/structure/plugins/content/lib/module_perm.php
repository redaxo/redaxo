<?php

/**
 * @package redaxo\structure\content
 */
class rex_module_perm extends rex_complex_perm
{
    /**
     * @param int $moduleId
     * @return bool
     */
    public function hasPerm($moduleId)
    {
        return $this->hasAll() || in_array($moduleId, $this->perms);
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
