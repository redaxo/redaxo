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
 * @version svn:$Id$
 */

class rex_var_category extends rex_var
{
  // --------------------------------- Output

  public function getTemplate($content)
  {
    return $this->matchCategory($content, true);
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
  private function matchCategory($content, $replaceInTemplate = false)
  {
  	$var = 'REX_CATEGORY';
    $matches = $this->getVarParams($content, $var);

    foreach ($matches as $match)
    {
    	list ($param_str, $args)   = $match;
      $category_id = $this->getArg('id',    $args, 0);
      // use ${xxx} notation so the var can be interpreted correctly when de-serialized
      $clang       = $this->getArg('clang', $args, 'rex_clang::getId()');
      $field       = $this->getArg('field', $args, '');

      $tpl = '';
      if($category_id == 0)
      {
        // REX_CATEGORY[field=name] feld von aktueller kategorie verwenden
      	if(rex_ooCategory::hasValue($field))
        {
          // bezeichner wählen, der keine variablen
          // aus modulen/templates überschreibt
          // beachte: root-artikel haben keine kategorie
          // clang als string übergeben wg ${xxx} notation
          $varname_art = '$__rex_art';
          $varname_cat = '$__rex_cat';
          $tpl = '<?php
          '. $varname_art .' = rex_ooArticle::getArticleById(rex::getProperty(\'article_id\'), "'. $clang .'");
          '. $varname_cat .' = '. $varname_art .'->getCategory();
          if('. $varname_cat .') echo htmlspecialchars('. $this->handleGlobalVarParamsSerialized($var, $args, $varname_cat .'->getValue(\''. addslashes($field) .'\')') .');
          ?>';
        }
      }
      else if($category_id > 0)
      {
        // REX_CATEGORY[field=name id=5] feld von gegebene category_id verwenden
      	if($field)
        {
          if(rex_ooCategory::hasValue($field))
          {
            // bezeichner wählen, der keine variablen
	          // aus modulen/templates überschreibt
            // clang als string übergeben wg ${xxx} notation
            $varname = '$__rex_cat';
	          $tpl = '<?php
	          '. $varname .' = rex_ooCategory::getCategoryById('. $category_id .', "'. $clang .'");
            if('. $varname .') echo htmlspecialchars('. $this->handleGlobalVarParamsSerialized($var, $args, $varname .'->getValue(\''. addslashes($field) .'\')') .');
	          ?>';
          }
        }
      }

      if($tpl != '')
        $content = str_replace($var . '[' . $param_str . ']', $tpl, $content);
    }

    return $content;
  }
}