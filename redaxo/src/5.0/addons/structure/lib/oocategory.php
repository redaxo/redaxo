<?php

/**
 * Object Oriented Framework: Bildet eine Kategorie der Struktur ab
 * @package redaxo4
 * @version svn:$Id$
 */

class rex_oocategory extends rex_ooredaxo
{
  public function __construct($params = false, $clang = false)
  {
    parent :: __construct($params, $clang);
  }

  /*
   * CLASS Function:
   * Return an rex_ooredaxo object based on an id
   */
  static public function getCategoryById($category_id, $clang = false)
  {
    return rex_ooarticle :: getArticleById($category_id, $clang, true);
  }

  /*
   * CLASS Function:
   * Return all Children by id
   */
  static public function getChildrenById($cat_parent_id, $ignore_offlines = false, $clang = false)
  {
    global $REX;

    $cat_parent_id = (int) $cat_parent_id;

    if($cat_parent_id < 0)
      return array();

    if ($clang === false)
      $clang = $REX['CUR_CLANG'];

    $categorylist = $REX['INCLUDE_PATH']."/generated/articles/".$cat_parent_id.".".$clang.".clist";

    $catlist = array ();

    if (!file_exists($categorylist))
    {
      // FIXME: find better path detection
      require_once dirname(__FILE__). '/../functions/function_rex_generate.inc.php';
//      require_once ($REX['INCLUDE_PATH'].'/core/functions/function_rex_generate.inc.php');
    	rex_generateLists($cat_parent_id);
    }

    if (file_exists($categorylist))
    {
      include ($categorylist);
      if (isset ($REX['RE_CAT_ID'][$cat_parent_id]) and is_array($REX['RE_CAT_ID'][$cat_parent_id]))
      {
        foreach ($REX['RE_CAT_ID'][$cat_parent_id] as $var)
        {
          $category = rex_oocategory :: getCategoryById($var, $clang);
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
  public function getPriority()
  {
    return $this->_catprior;
  }

  /**
   * CLASS Function:
   * Return a list of top level categories, ie.
   * categories that have no parent.
   * Returns an array of rex_oocategory objects sorted by $prior.
   *
   * If $ignore_offlines is set to TRUE,
   * all categories with status 0 will be
   * excempt from this list!
   */
  static public function getRootCategories($ignore_offlines = false, $clang = false)
  {
    global $REX;

    if ($clang === false)
      $clang = $REX['CUR_CLANG'];

    return rex_oocategory :: getChildrenById(0, $ignore_offlines, $clang);
  }

  /*
   * Object Function:
   * Return a list of all subcategories.
   * Returns an array of rex_ooredaxo objects sorted by $prior.
   *
   * If $ignore_offlines is set to TRUE,
   * all categories with status 0 will be
   * excempt from this list!
   */
  public function getChildren($ignore_offlines = false, $clang = false)
  {
    if ($clang === false)
      $clang = $this->_clang;

    return rex_oocategory :: getChildrenById($this->_id, $ignore_offlines, $clang);
  }

  /*
   * Object Function:
   * Returns the parent category
   */
  public function getParent($clang = false)
  {
    if ($clang === false)
      $clang = $this->_clang;

    return rex_oocategory :: getCategoryById($this->_re_id, $clang);
  }

  /*
   * Object Function:
   * Returns TRUE if this category is the direct
   * parent of the other category.
   */
  public function isParent($other_cat)
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
  public function isAncestor($other_cat)
  {
    $category = rex_oocategory :: _getCategoryObject($other_cat);
    return in_array($this->_id, explode('|', $category->getPath()));
  }

  /*
   * Object Function:
   * Return a list of articles in this category
   * Returns an array of rex_ooarticle objects sorted by $prior.
   *
   * If $ignore_offlines is set to TRUE,
   * all articles with status 0 will be
   * excempt from this list!
   */
  public function getArticles($ignore_offlines = false)
  {
    return rex_ooarticle :: getArticlesOfCategory($this->_id, $ignore_offlines, $this->_clang);
  }

  /*
   * Object Function:
   * Return the start article for this category
   */
  public function getStartArticle()
  {
    return rex_ooarticle :: getCategoryStartArticle($this->_id, $this->_clang);
  }

  /*
   * Accessor Method:
   * returns the name of the article
   */
  public function getName()
  {
    return $this->_catname;
  }

  /*
   * Accessor Method:
   * returns the path of the category
   */
  public function getPath()
  {
    return $this->_path;
  }

  /*
   * Accessor Method:
   * returns the path ids of the category as an array
   */
  public function getPathAsArray()
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

  static public function _getCategoryObject($category, $clang = false)
  {
    if (is_object($category))
    {
      return $category;
    }
    elseif (is_int($category))
    {
      return rex_oocategory :: getCategoryById($category, $clang);
    }
    elseif (is_array($category))
    {
      $catlist = array ();
      foreach ($category as $cat)
      {
        $catobj = rex_oocategory :: _getCategoryObject($cat, $clang);
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

  public function hasValue($value)
  {
    return parent::_hasValue($value, array('cat_'));
  }

  /*
   * Static Method:
   * Returns True if the given category is a valid rex_oocategory
   */
  static public function isValid($category)
  {
    return is_object($category) && is_a($category, 'rex_oocategory');
  }

  /*
   * Static Method:
   * Returns an array containing all templates which are available for the given category_id.
   * if the category_id is non-positive all templates in the system are returned.
   * if the category_id is invalid an empty array is returned.
   *
   */
  static public function getTemplates($category_id, $ignore_inactive = true)
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
    	if($c = rex_oocategory::getCategoryById($category_id))
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