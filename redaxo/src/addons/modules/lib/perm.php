<?php

class rex_module_perm extends rex_complex_perm
{
  public function hasPerm($module_id)
  {
    return $this->hasAll() || in_array($module_id, $this->perms);
  }

  static public function getFieldParams()
  {
    return array(
      'label' => rex_i18n::msg('modules'),
      'all_label' => rex_i18n::msg('all_modules'),
      'sql_options' => 'select name, id from ' . rex::getTablePrefix() . 'module order by name'
    );
  }
}
