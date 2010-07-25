<?php

/**
 * REX_ARTICLE[1]
 * REX_ARTICLE[id=1]
 * 
 * REX_ARTICLE[id=1 ctype=2 clang=1]
 * 
 * REX_ARTICLE[field='id']
 * REX_ARTICLE[field='description' id=3]
 * REX_ARTICLE[field='description' id=3 clang=2]
 * 
 * Attribute:
 *   - clang     => ClangId des Artikels festlegen
 *   - ctype     => Spalte des Artikels festlegen
 *   - field     => Nur dieses Feld des Artikels ausgeben
 *
 * @package redaxo4
 * @version svn:$Id$
 */

class rex_var_article extends rex_var
{
  // --------------------------------- Output

  /*public*/ function getTemplate($content)
  {
    return $this->matchArticle($content, true);
  }

  /*public*/ function getBEOutput(& $sql, $content)
  {
    return $this->matchArticle($content);
  }

  /*protected*/ function handleDefaultParam($varname, $args, $name, $value)
  {
    switch($name)
    {
      case '1' :
      case 'clang' :
        $args['clang'] = (int) $value;
        break;
      case '2' :
      case 'ctype' :
        $args['ctype'] = (int) $value;
        break;
      case 'field' :
        $args['field'] = (string) $value;
        break;
    }
    return parent::handleDefaultParam($varname, $args, $name, $value);
  }
  
  /**
   * Werte für die Ausgabe
   */
  /*private*/ function matchArticle($content, $replaceInTemplate = false)
  {
  	global $REX;

    $var = 'REX_ARTICLE';
    $matches = $this->getVarParams($content, $var);
    
    foreach ($matches as $match)
    {
      list ($param_str, $args)  = $match;
      list ($article_id, $args) = $this->extractArg('id',    $args, 0);
      list ($clang, $args)      = $this->extractArg('clang', $args, '$REX[\'CUR_CLANG\']');
      list ($ctype, $args)      = $this->extractArg('ctype', $args, -1);
      list ($field, $args)      = $this->extractArg('field', $args, '');
      
      $tpl = '';
      if($article_id == 0)
      {
        // REX_ARTICLE[field=name] keine id -> feld von aktuellem artikel verwenden
      	if($field)
	      {
	        if(OOArticle::hasValue($field))
	        {
	          $tpl = '<?php echo htmlspecialchars('. $this->handleGlobalVarParamsSerialized($var, $args, '$this->getValue(\''. addslashes($field) .'\')') .'); ?>';
	        }
	      }
	      // REX_ARTICLE[] keine id -> aktuellen artikel verwenden
	      else
	      {
	      	if($replaceInTemplate)
	      	{
	          // aktueller Artikel darf nur in Templates, nicht in Modulen eingebunden werden
	          // => endlossschleife
	          $tpl = '<?php echo '. $this->handleGlobalVarParamsSerialized($var, $args, '$this->getArticle('. $ctype .')') .'; ?>';
	      	}
	      }
      }
      else if($article_id > 0)
      {
        // REX_ARTICLE[field=name id=5] feld von gegebene artikel id verwenden
      	if($field)
        {
          if(OOArticle::hasValue($field))
          {
	        	// bezeichner wählen, der keine variablen
	          // aus modulen/templates überschreibt
	          $varname = '$__rex_art';
	          $tpl = '<?php
	          '. $varname .' = OOArticle::getArticleById('. $article_id .', '. $clang .');
	          if('. $varname .') echo htmlspecialchars('. $this->handleGlobalVarParamsSerialized($var, $args, $varname .'->getValue(\''. addslashes($field) .'\')') .');
	          ?>';
          }
        }
        // REX_ARTICLE[id=5] kompletten artikel mit gegebener artikel id einbinden
        else
        {
	        // bezeichner wählen, der keine variablen
	        // aus modulen/templates überschreibt
	        $varname = '$__rex_art';
	        $tpl = '<?php
	        '. $varname .' = new rex_article();
	        '. $varname .'->setArticleId('. $article_id .');
	        '. $varname .'->setClang('. $clang .');
          echo '. $this->handleGlobalVarParamsSerialized($var, $args, $varname .'->getArticle('. $ctype .')') .';
	        ?>';
        }
      }
      
      if($tpl != '')
        $content = str_replace($var . '[' . $param_str . ']', $tpl, $content);
    }

    return $content;
  }
}