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
 * @package redaxo5
 * @version svn:$Id$
 */

class rex_var_article extends rex_var
{
  // --------------------------------- Output

  public function getTemplate($content)
  {
    return $this->matchArticle($content, true);
  }

  public function getBEOutput(rex_sql $sql, $content)
  {
    return $this->matchArticle($content);
  }

  static public function handleDefaultParam($varname, array $args, $name, $value)
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
  private function matchArticle($content, $replaceInTemplate = false)
  {
  	$var = 'REX_ARTICLE';
    $matches = $this->getVarParams($content, $var);

    foreach ($matches as $match)
    {
      list ($param_str, $args)  = $match;
      $article_id = $this->getArg('id',    $args, 0);
      $clang      = $this->getArg('clang', $args, 'null');
      $ctype      = $this->getArg('ctype', $args, -1);
      $field      = $this->getArg('field', $args, '');

      $tpl = '';
      if($article_id > 0)
      {
        $article = $article_id;
      }
      else if($clang == 'null')
      {
        $article = '$this->getValue(\'article_id\')';
      }
      else
      {
        $article = '$this';
      }

      if($field)
      {
        if(rex_ooArticle::hasValue($field))
        {
          $tpl = '<?php echo '. __CLASS__ .'::getArticleValue('. $article .", '". $field ."', ". $clang .", '". json_encode($args) ."'); ?>";
        }
      }
      else
      {
        if($article != 0 || $replaceInTemplate)
        {
          // aktueller Artikel darf nur in Templates, nicht in Modulen eingebunden werden
          // => endlossschleife
          $tpl = '<?php echo '. __CLASS__ .'::getArticle('. $article .', '. $ctype .', '. $clang .", '". json_encode($args) ."'); ?>";
        }
      }

      if($tpl != '')
        $content = str_replace($var . '[' . $param_str . ']', $tpl, $content);
    }

    return $content;
  }

  static public function getArticleValue($article, $field, $clang = null, $args = '')
  {
    if($clang === null)
    {
      $clang = rex_clang::getId();
    }
    if(!is_object($article))
    {
      $article = rex_ooArticle::getArticleById($article, $clang);
    }

    $value = $article->getValue($field);
    $value = self::handleGlobalVarParams('REX_ARTICLE', json_decode($args, true), $value);
    return htmlspecialchars($value);
  }

  static public function getArticle($article, $ctype = -1, $clang = null, $args = '')
  {
    if($clang === null)
    {
      $clang = rex_clang::getId();
    }
    if(!is_object($article))
    {
      $article = new rex_article($article, $clang);
    }

    $article = $article->getArticle($ctype);
    $article = self::handleGlobalVarParams('REX_ARTICLE', json_decode($args, true), $article);
    return $article;
  }
}