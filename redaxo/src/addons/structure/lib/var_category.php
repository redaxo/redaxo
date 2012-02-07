<?php

/**
 * REX_CATEGORY[xzy]
 * REX_CATEGORY[field=xzy]
 * REX_CATEGORY[field=xzy id=3]
 * REX_CATEGORY[field=xzy id=3 clang=2]
 *
 * Attribute:
 *   - field    => Feld der Kategorie, das ausgegeben werden soll
 *   - clang    => ClangId der Kategorie
 *
 *
 * @package redaxo5
 */

class rex_var_category extends rex_var
{
  /**
   * Werte für die Ausgabe
   */
  protected function getOutput()
  {
    $field = $this->getArg('field', 'string');
    if(!rex_ooCategory::hasValue($field))
      return false;

    $category_id = $this->getArg('id', 'int', '$this->getValue(\'category_id\')');
    $clang = $this->getArg('clang', 'int', 'null');

    return __CLASS__ .'::getCategoryValue('. $category_id .", '". addslashes($field) ."', ". $clang .')';
  }

  static public function getCategoryValue($id, $field, $clang = null)
  {
    if($clang === null)
    {
      $clang = rex_clang::getId();
    }
    $cat = rex_ooCategory::getCategoryById($id, $clang);
    if($cat)
    {
      return htmlspecialchars($cat->getValue($field));
    }
  }
}