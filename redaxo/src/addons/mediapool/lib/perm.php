<?php

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

  static public function getFieldParams()
  {
    return array(
      'label' => rex_i18n::msg('mediafolder'),
      'all_label' => rex_i18n::msg('all_mediafolder'),
      'select' => new rex_media_category_select(false)
    );
  }
}
