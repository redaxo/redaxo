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
  // --------------------------------- Output

  public function getTemplate($content)
  {
    return $this->matchCategory($content);
  }

  public function getBEOutput(rex_sql $sql, $content)
  {
    return $this->matchCategory($content);
  }

  static public function handleDefaultParam($varname, array $args, $name, $value)
  {
    switch($name)
    {
      case 'field' :
        $args['field'] = (string) $value;
        break;
      case 'clang' :
        $args['clang'] = (int) $value;
        break;
    }
    return parent::handleDefaultParam($varname, $args, $name, $value);
  }

  /**
   * Werte für die Ausgabe
   */
  private function matchCategory($content)
  {
    $var = 'REX_CATEGORY';
    $matches = $this->getVarParams($content, $var);

    foreach ($matches as $match)
    {
      list ($param_str, $args)   = $match;
      $category_id = $this->getArg('id',    $args, 0);
      $clang       = $this->getArg('clang', $args, 'null');
      $field       = $this->getArg('field', $args, '');

      $tpl = '';
      if(rex_ooCategory::hasValue($field))
      {
        $tpl = '<?php echo '. __CLASS__ .'::getCategory('. $category_id .", '". addslashes($field) ."', ". $clang .", '". json_encode($args) ."'); ?>";
      }

      if($tpl != '')
        $content = str_replace($var . '[' . $param_str . ']', $tpl, $content);
    }

    return $content;
  }

  static public function getCategory($id, $field, $clang = null, $args = '')
  {
    if($clang === null)
    {
      $clang = rex_clang::getId();
    }
    if($id === 0)
    {
      $art = rex_ooArticle::getArticleById(rex::getProperty('article_id'), $clang);
      $cat = $art->getCategory();
    }
    else if($id > 0)
    {
      $cat = rex_ooCategory::getCategoryById($id, $clang);
    }

    if($cat)
    {
      $cat = self::handleGlobalVarParams('REX_CATEGORY', json_decode($args, true), $cat->getValue($field));
      return htmlspecialchars($cat);
    }
  }
}
