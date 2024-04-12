<?php

namespace Redaxo\Core\Content;

use Redaxo\Core\Core;
use Redaxo\Core\Security\ComplexPermission;
use Redaxo\Core\Translation\I18n;

use function in_array;

class ModulePermission extends ComplexPermission
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
            'label' => I18n::msg('modules'),
            'all_label' => I18n::msg('all_modules'),
            'sql_options' => 'select name, id from ' . Core::getTablePrefix() . 'module order by name',
        ];
    }
}
