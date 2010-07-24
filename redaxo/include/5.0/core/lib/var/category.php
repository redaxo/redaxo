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
 * @package redaxo4
 * @version svn:$Id$
 */

class rex_var_category extends rex_var
{
  // --------------------------------- Output

  /*public*/ function getTemplate($content)
  {
    return $this->matchCategory($content, true);
  }

  /*public*/ function getBEOutput(& $sql, $content)
  {
    return $this->matchCategory($content);
  }

  /*protected*/ function handleDefaultParam($varname, $args, $name, $value)
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
  /*private*/ function matchCategory($content, $replaceInTemplate = false)
  {
  	global $REX;

    $var = 'REX_CATEGORY';
    $matches = $this->getVarParams($content, $var);
    
    foreach ($matches as $match)
    {
    	list ($param_str, $args)   = $match;
      list ($category_id, $args) = $this->extractArg('id',    $args, 0);
      list ($clang, $args)       = $this->extractArg('clang', $args, '$REX[\'CUR_CLANG\']');
      list ($field, $args)       = $this->extractArg('field', $args, '');
      
      $tpl = '';
      if($category_id == 0)
      {
        // REX_CATEGORY[field=name] feld von aktueller kategorie verwenden
      	if(OOCategory::hasValue($field))
        {
          // bezeichner wählen, der keine variablen
          // aus modulen/templates überschreibt
          // beachte: root-artikel haben keine kategorie
          $varname_art = '$__rex_art';
          $varname_cat = '$__rex_cat';
          $tpl = '<?php
          '. $varname_art .' = OOArticle::getArticleById($REX[\'ARTICLE_ID\'], '. $clang .');
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
          if(OOCategory::hasValue($field))
          {
            // bezeichner wählen, der keine variablen
	          // aus modulen/templates überschreibt
	          $varname = '$__rex_cat';
	          $tpl = '<?php
	          '. $varname .' = OOCategory::getCategoryById('. $category_id .', '. $clang .');
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