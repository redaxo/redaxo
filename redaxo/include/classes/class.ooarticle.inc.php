<?php

/**
 * Object Oriented Framework: Bildet einen Artikel der Struktur ab
 * @package redaxo4
 * @version svn:$Id$
 */

class OOArticle extends OORedaxo
{
  /*protected*/ function OOArticle($params = FALSE, $clang = FALSE)
  {
    parent :: OORedaxo($params, $clang);
  }

  /**
   * CLASS Function:
   * Return an OORedaxo object based on an id
   */
  /*public static*/ function getArticleById($article_id, $clang = FALSE, $OOCategory = FALSE)
  {
    global $REX;
    
    $article_id = (int) $article_id;
    
    if(!is_int($article_id))
      return NULL;
      
    if ($clang === FALSE)
      $clang = $REX['CUR_CLANG'];
    
    $article_path = $REX['INCLUDE_PATH'].'/generated/articles/'.$article_id.'.'.$clang.'.article';
    if (!file_exists($article_path))
		{
			require_once ($REX['INCLUDE_PATH'].'/functions/function_rex_generate.inc.php');
    	rex_generateArticleMeta($article_id, $clang);
		}

    if (file_exists($article_path))
    {
      require_once ($article_path);
      
      if ($OOCategory)
        return new OOCategory(OORedaxo :: convertGeneratedArray($REX['ART'][$article_id], $clang));
      else
        return new OOArticle(OORedaxo :: convertGeneratedArray($REX['ART'][$article_id], $clang));
    }
    
    return NULL;
  }

  /**
   * CLASS Function:
   * Return the site wide start article
   */
  /*public static*/ function getSiteStartArticle($clang = FALSE)
  {
    global $REX;
    
    if ($clang === FALSE)
      $clang = $REX['CUR_CLANG'];
      
    return OOArticle :: getArticleById($REX['START_ARTICLE_ID'], $clang);
  }

  /**
   * CLASS Function:
   * Return start article for a certain category
   */
  /*public static*/ function getCategoryStartArticle($a_category_id, $clang = FALSE)
  {
    global $REX;
    
    if ($clang === FALSE)
      $clang = $REX['CUR_CLANG'];
      
    return OOArticle :: getArticleById($a_category_id, $clang);
  }

  /**
   * CLASS Function:
   * Return a list of articles for a certain category
   */
  /*public static*/ function getArticlesOfCategory($a_category_id, $ignore_offlines = FALSE, $clang = FALSE)
  {
    global $REX;

    if ($clang === FALSE)
      $clang = $REX['CUR_CLANG'];

    $articlelist = $REX['INCLUDE_PATH']."/generated/articles/".$a_category_id.".".$clang.".alist";
    if(!file_exists($articlelist))
    {
			include_once ($REX['INCLUDE_PATH'].'/functions/function_rex_generate.inc.php');
      rex_generateLists($a_category_id, $clang);
    }

    $artlist = array ();
    if(file_exists($articlelist))
    {
      include_once($articlelist);
      
      if(isset($REX['RE_ID'][$a_category_id]))
      {
  	    foreach ($REX['RE_ID'][$a_category_id] as $var)
  	    {
  	      $article = OOArticle :: getArticleById($var, $clang);
  	      if ($ignore_offlines)
  	      {
  	        if ($article->isOnline())
  	        {
  	          $artlist[] = $article;
  	        }
  	      }
  	      else
  	      {
  	        $artlist[] = $article;
  	      }
  	    }
      }
    }

    return $artlist;
  }

  /**
   * CLASS Function:
   * Return a list of top-level articles
   */
  /*public static*/ function getRootArticles($ignore_offlines = FALSE, $clang = FALSE)
  {
    return OOArticle :: getArticlesOfCategory(0, $ignore_offlines, $clang);
  }

  /**
   * Accessor Method:
   * returns the category id
   */
  /*public*/ function getCategoryId()
  {
    return $this->isStartPage() ? $this->getId() : $this->getParentId();
  }

  /*
   * Object Function:
   * Returns the parent category
   */
  /*public*/ function getCategory()
  {
    return OOCategory :: getCategoryById($this->getCategoryId(), $this->getClang());
  }

  /**
   * Accessor Method:
   * returns the path of the category/article
   */
  /*public*/ function getPath()
  {
      if($this->isStartArticle())
        return $this->_path.$this->_id .'|';
        
      return $this->_path;
  }
  
  /**
   * Accessor Method:
   * returns the path ids of the category/article as an array
   */
  /*public*/ function getPathAsArray()
  {
    $p = $this->_path;
    if($this->isStartArticle())
      $p = $this->_path.$this->_id .'|';
      
  	foreach($p as $k => $v)
  	{
  		if($v == "")
  			unset($p[$k]);
  		else
  		  $p[$k] = (int) $v;
  	}
  	
    return array_values($p);
  }
  
  /*
   * Static Method: Returns True when the given article is a valid OOArticle
   */
  /*public static*/ function isValid($article)
  {
    return is_object($article) && is_a($article, 'ooarticle');
  }

  /*public*/ function getValue($value)
  {
    // alias für re_id -> category_id
    if(in_array($value, array('re_id', '_re_id', 'category_id', '_category_id')))
    {
      // für die CatId hier den Getter verwenden,
      // da dort je nach ArtikelTyp unterscheidungen getroffen werden müssen
      return $this->getCategoryId();
    }
    return parent::getValue($value);
  }
  
  /*public*/ function hasValue($value)
  {
  	return parent::hasValue($value, array('art_'));
  }
  
}