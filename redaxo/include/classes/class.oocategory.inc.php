<?php

/**
 * Object Oriented Framework: Bildet eine Kategorie der Struktur ab
 * @package redaxo4
 * @version svn:$Id$
 */

class OOCategory extends OORedaxo
{
  /*protected*/ function OOCategory($params = false, $clang = false)
  {
    parent :: OORedaxo($params, $clang);
  }

  /*
   * CLASS Function:
   * Return an OORedaxo object based on an id
   */
  /*public static*/ function getCategoryById($category_id, $clang = false)
  {
    return OOArticle :: getArticleById($category_id, $clang, true);
  }

  /*
   * CLASS Function:
   * Return all Children by id
   */
  /*public static*/ function getChildrenById($cat_parent_id, $ignore_offlines = false, $clang = false)
  {
    global $REX;
    
    $cat_parent_id = (int) $cat_parent_id;

    if(!is_int($cat_parent_id))
      return array();
    
    if ($clang === false)
      $clang = $REX['CUR_CLANG'];

    $categorylist = $REX['INCLUDE_PATH']."/generated/articles/".$cat_parent_id.".".$clang.".clist";

    $catlist = array ();

    if (!file_exists($categorylist))
    {
    	include_once ($REX["INCLUDE_PATH"]."/functions/function_rex_generate.inc.php");
    	rex_generateLists($cat_parent_id);
    }

    if (file_exists($categorylist))
    {
      include ($categorylist);
      if (isset ($REX['RE_CAT_ID'][$cat_parent_id]) and is_array($REX['RE_CAT_ID'][$cat_parent_id]))
      {
        foreach ($REX['RE_CAT_ID'][$cat_parent_id] as $var)
        {
          $category = OOCategory :: getCategoryById($var, $clang);
          if ($ignore_offlines)
          {
            if ($category->isOnline())
            {
              $catlist[] = $category;
            }
          }
          else
          {
            $catlist[] = $category;
          }
        }
      }
    }

    return $catlist;
  }

  /*
   * Accessor Method:
   * returns the article priority
   */
  /*public*/ function getPriority()
  {
    return $this->_catprior;
  }

  /**
   * CLASS Function:
   * Return a list of top level categories, ie.
   * categories that have no parent.
   * Returns an array of OOCategory objects sorted by $prior.
   *
   * If $ignore_offlines is set to TRUE,
   * all categories with status 0 will be
   * excempt from this list!
   */
  /*public static*/ function getRootCategories($ignore_offlines = false, $clang = false)
  {
    global $REX;

    if ($clang === false)
      $clang = $REX['CUR_CLANG'];

    return OOCategory :: getChildrenById(0, $ignore_offlines, $clang);
  }

  /*
   * Object Function:
   * Return a list of all subcategories.
   * Returns an array of OORedaxo objects sorted by $prior.
   *
   * If $ignore_offlines is set to TRUE,
   * all categories with status 0 will be
   * excempt from this list!
   */
  /*public*/ function getChildren($ignore_offlines = false, $clang = false)
  {
    if ($clang === false)
      $clang = $this->_clang;

    return OOCategory :: getChildrenById($this->_id, $ignore_offlines, $clang);
  }

  /*
   * Object Function:
   * Returns the parent category
   */
  /*public*/ function getParent($clang = false)
  {
    if ($clang === false)
      $clang = $this->_clang;

    return OOCategory :: getCategoryById($this->_re_id, $clang);
  }

  /*
   * Object Function:
   * Returns TRUE if this category is the direct
   * parent of the other category.
   */
  /*public*/ function isParent($other_cat)
  {
     return $this->getId() == $other_cat->getParentId() &&
            $this->getClang() == $other_cat->getClang();
  }

  /*
   * Object Function:
   * Returns TRUE if this category is an ancestor
   * (parent, grandparent, greatgrandparent, etc)
   * of the other category.
   */
  /*public*/ function isAncestor($other_cat)
  {
    $category = OOCategory :: _getCategoryObject($other_cat);
    return in_array($this->_id, explode('|', $category->getPath()));
  }

  /*
   * Object Function:
   * Return a list of articles in this category
   * Returns an array of OOArticle objects sorted by $prior.
   *
   * If $ignore_offlines is set to TRUE,
   * all articles with status 0 will be
   * excempt from this list!
   */
  /*public*/ function getArticles($ignore_offlines = false)
  {
    return OOArticle :: getArticlesOfCategory($this->_id, $ignore_offlines, $this->_clang);
  }

  /*
   * Object Function:
   * Return the start article for this category
   */
  /*public*/ function getStartArticle()
  {
    return OOArticle :: getCategoryStartArticle($this->_id, $this->_clang);
  }

  /*
   * Accessor Method:
   * returns the name of the article
   */
  /*public*/ function getName()
  {
    return $this->_catname;
  }

  /*
   * Accessor Method:
   * returns the path of the category
   */
  /*public*/ function getPath()
  {
    return $this->_path;
  }
  
  /*
   * Accessor Method:
   * returns the path ids of the category as an array
   */
  /*public*/ function getPathAsArray()
  {
  	$p = explode('|',$this->_path);
  	foreach($p as $k => $v)
  	{
  		if($v == '')
  			unset($p[$k]);
  		else
  		  $p[$k] = (int) $v;
  	}
  	
    return array_values($p);
  }
  
  /*public static*/ function & _getCategoryObject($category, $clang = false)
  {
    if (is_object($category))
    {
      return $category;
    }
    elseif (is_int($category))
    {
      return OOCategory :: getCategoryById($category, $clang);
    }
    elseif (is_array($category))
    {
      $catlist = array ();
      foreach ($category as $cat)
      {
        $catobj = OOCategory :: _getCategoryObject($cat, $clang);
        if (is_object($catobj))
        {
          $catlist[] = $catobj;
        }
        else
        {
          return null;
        }
      }
      return $catlist;
    }
    return null;
  }

  /*public*/ function hasValue($value)
  {
    return parent::hasValue($value, array('cat_'));
  }
  
  /*
   * Static Method:
   * Returns True if the given category is a valid OOCategory
   */
  /*public*/ function isValid($category)
  {
    return is_object($category) && is_a($category, 'oocategory');
  }
  
  /*
   * Static Method:
   * Returns an array containing all templates which are available for the given category_id.
   * if the category_id is non-positive all templates in the system are returned.
   * if the category_id is invalid an empty array is returned.
   * 
   */
  /*public static*/ function getTemplates($category_id, $ignore_inactive = true)
  {
    global $REX;

    $ignore_inactive = $ignore_inactive ? 1 : 0;
    
    $templates = array();
    $t_sql = rex_sql::factory();
    $t_sql->setQuery('select id,name,attributes from '.$REX['TABLE_PREFIX'].'template where active='. $ignore_inactive .' order by name');

    if($category_id < 1)
    {
    	// Alle globalen Templates
    	foreach($t_sql->getArray() as $t)
    	{
        $categories = rex_getAttributes("categories", $t["attributes"]);
        if (!is_array($categories) || $categories["all"] == 1)
    		  $templates[$t["id"]] = $t['name'];
    	}
    }else
    {
    	if($c = OOCategory::getCategoryById($category_id))
    	{
    		$path = $c->getPathAsArray();
    		$path[] = $category_id;
	    	foreach($t_sql->getArray() as $t)
	    	{
	    		$categories = rex_getAttributes("categories", $t["attributes"]);
	    		// template ist nicht kategoriespezifisch -> includen
	    		if(!is_array($categories) || $categories["all"] == 1)
	    		{
            $templates[$t["id"]] = $t['name'];
	    		}
	    		else
	    		{
	    		  // template ist auf kategorien beschraenkt..
	    		  // nachschauen ob eine davon im pfad der aktuellen kategorie liegt
  	    		foreach($path as $p)
  	    		{
  	    			if(in_array($p,$categories))
  	    			{
  	    				$templates[$t["id"]] = $t['name'];
  	    				break;
  	    			}
  	    		}
	    		}
	    	}
    	}
    }
    return $templates;
  }
}