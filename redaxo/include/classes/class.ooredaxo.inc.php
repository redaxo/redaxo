<?php

/**
 * Object Oriented Framework: Basisklasse für die Strukturkomponenten
 * @package redaxo4
 * @version svn:$Id$
 */

/*abstract*/ class OORedaxo
{
  /*
   * these vars get read out
   */
  var $_id = '';
  var $_re_id = '';
  var $_clang = '';
  var $_name = '';
  var $_catname = '';
  var $_template_id = '';
  var $_path = '';
  var $_prior = '';
  var $_startpage = '';
  var $_status = '';
  var $_attributes = '';
  var $_updatedate = '';
  var $_createdate = '';
  var $_updateuser = '';
  var $_createuser = '';

  /*
   * Constructor
   */
  /*protected*/ function OORedaxo($params = false, $clang = false)
  {
    if ($params !== false)
    {
      foreach (OORedaxo :: getClassVars() as $var)
      {
        if(isset($params[$var]))
        {
          $class_var = '_'.$var;
          $value = $params[$var];
          $this->$class_var = $value;
        }
      }
    }

    if ($clang !== false)
    {
      $this->setClang($clang);
    }
  }

  /*public*/ function setClang($clang)
  {
    $this->_clang = $clang;
  }

  /*
   * Class Function:
   * Returns Object Value
   */
  /*public*/ function getValue($value)
  {
    // damit alte rex_article felder wie teaser, online_from etc
    // noch funktionieren
    // gleicher BC code nochmals in article::getValue
    foreach(array('_', 'art_', 'cat_') as $prefix)
    {
    	$val = $prefix . $value;
    	if(isset($this->$val))
    	{
    	  return $this->$val;
    	}
    }
    return null;
  }

  /*public*/ function hasValue($value, $prefixes = array())
  {
    static $values = null;
        
    if(!$values)
    {
      $values = OORedaxo :: getClassVars();
    }
    foreach(array_merge(array(''), $prefixes) as $prefix)
    {
      if (in_array($prefix . $value, $values))
      {
      	return true;
      }
    }
    return false;
  }

  /**
   * CLASS Function:
   * Returns an Array containing article field names
   */
  /*public static*/ function getClassVars()
  {
    static $vars = array ();

    if (empty($vars))
    {
      global $REX;

      $vars = array();

      $file = $REX['INCLUDE_PATH']. '/generated/articles/'.  $REX['START_ARTICLE_ID'] .'.0.article';
      if($REX['GG'] && file_exists($file))
      {
        // Im GetGenerated Modus, die Spaltennamen aus den generated Dateien holen
        include_once($file);

        // da getClassVars() eine statische Methode ist, können wir hier nicht mit $this->getId() arbeiten!
        $genVars = OORedaxo::convertGeneratedArray($REX['ART'][$REX['START_ARTICLE_ID']],0);
        unset($genVars['article_id']);
        unset($genVars['last_update_stamp']);
        foreach($genVars as $name => $value)
        {
          $vars[] = $name;
        }
      }
      else
      {
        // Im Backend die Spalten aus der DB auslesen / via EP holen
        $sql = rex_sql::factory();
        $sql->setQuery('SELECT * FROM '. $REX['TABLE_PREFIX'] .'article LIMIT 0');
        foreach($sql->getFieldnames() as $field)
        {
          $vars[] = $field;
        }
      }
    }

    return $vars;
  }

  /*
  * CLASS Function:
  * Converts Genernated Array to OOBase Format Array
  */
  /*public static*/ function convertGeneratedArray($generatedArray, $clang)
  {
    $OORedaxoArray['id'] = $generatedArray['article_id'][$clang];
    $OORedaxoArray['clang'] = $clang;
    foreach ($generatedArray as $key => $var)
    {
      $OORedaxoArray[$key] = $var[$clang];
    }
    unset ($OORedaxoArray['_article_id']);
    return $OORedaxoArray;
  }

  /*
   * Accessor Method:
   * returns the clang of the category
   */
  /*public*/ function getClang()
  {
    return $this->_clang;
  }

  /*
   * Object Helper Function:
   * Returns a url for linking to this article
   */
  /*public*/ function getUrl($params = '', $divider = '&amp;')
  {
    return rex_getUrl($this->getId(), $this->getClang(), $params, $divider);
  }

  /*
   * Accessor Method:
   * returns the id of the article
   */
  /*public*/ function getId()
  {
    return $this->_id;
  }

  /*
   * Accessor Method:
   * returns the parent_id of the article
   */
  /*public*/ function getParentId()
  {
    return $this->_re_id;
  }

  /*
   * Accessor Method:
   * returns the parent object of the article
   */
  /*public*/ function getParent()
  {
    return OOArticle::getArticleById($this->_re_id);
  }

  /*
   * Accessor Method:
   * returns the name of the article
   */
  /*public*/ function getName()
  {
    return $this->_name;
  }

  /**
   * Accessor Method:
   * returns the name of the article
   * @deprecated 4.0 17.09.2007
   */
  /*public*/ function getFile()
  {
    return $this->getValue('art_file');
  }

  /**
   * Accessor Method:
   * returns the name of the article
   * @deprecated 4.0 17.09.2007
   */
  /*public*/ function getFileMedia()
  {
    return OOMedia :: getMediaByFileName($this->getValue('art_file'));
  }

  /**
   * Accessor Method:
   * returns the article description.
   * @deprecated 4.0 17.09.2007
   */
  /*public*/ function getDescription()
  {
    return $this->getValue('art_description');
  }

  /**
   * Accessor Method:
   * returns the Type ID of the article.
   * @deprecated 4.0 17.09.2007
   */
  /*public*/ function getTypeId()
  {
    return $this->getValue('art_type_id');
  }

  /*
   * Accessor Method:
   * returns the article priority
   */
  /*public*/ function getPriority()
  {
    return $this->_prior;
  }

  /*
   * Accessor Method:
   * returns the last update user
   */
  /*public*/ function getUpdateUser()
  {
    return $this->_updateuser;
  }

  /*
   * Accessor Method:
   * returns the last update date
   */
  /*public*/ function getUpdateDate($format = null)
  {
    return OOMedia :: _getDate($this->_updatedate, $format);
  }

  /*
   * Accessor Method:
   * returns the creator
   */
  /*public*/ function getCreateUser()
  {
    return $this->_createuser;
  }

  /*
   * Accessor Method:
   * returns the creation date
   */
  /*public*/ function getCreateDate($format = null)
  {
    return OOMedia :: _getDate($this->_createdate, $format);
  }

  /*
   * Accessor Method:
   * returns true if article is online.
   */
  /*public*/ function isOnline()
  {
    return $this->_status == 1;
  }

  /*
   * Accessor Method:
   * returns true if article is offline.
   */
  /*public*/ function isOffline()
  {
    return $this->_status == 0;
  }

  /*
   * Accessor Method:
   * returns the template id
   */
  /*public*/ function getTemplateId()
  {
    return $this->_template_id;
  }

  /*
   * Accessor Method:
   * returns true if article has a template.
   */
  /*public*/ function hasTemplate()
  {
	return $this->_template_id > 0;
  }

  /*
   * Accessor Method:
   * Returns a link to this article
   *
   * @param [$params] Parameter für den Link
   * @param [$attributes] array Attribute die dem Link hinzugefügt werden sollen. Default: null
   * @param [$sorround_tag] string HTML-Tag-Name mit dem der Link umgeben werden soll, z.b. 'li', 'div'. Default: null
   * @param [sorround_attributes] array Attribute die Umgebenden-Element hinzugefügt werden sollen. Default: null
   */
  /*public*/ function toLink($params = '', $attributes = null, $sorround_tag = null, $sorround_attributes = null)
  {
    $name = htmlspecialchars($this->getName());
    $link = '<a href="'.$this->getUrl($params).'"'.$this->_toAttributeString($attributes).' title="'.$name.'">'.$name.'</a>';

    if ($sorround_tag !== null && is_string($sorround_tag))
    {
      $link = '<'.$sorround_tag.$this->_toAttributeString($sorround_attributes).'>'.$link.'</'.$sorround_tag.'>';
    }

    return $link;
  }

  /*protected*/ function _toAttributeString($attributes)
  {
    $attr = '';

    if ($attributes !== null && is_array($attributes))
    {
      foreach ($attributes as $name => $value)
      {
        $attr .= ' '.$name.'="'.$value.'"';
      }
    }

    return $attr;
  }

  /*
   * Object Function:
   * Get an array of all parentCategories.
   * Returns an array of OORedaxo objects sorted by $prior.
   */
  /*public*/ function getParentTree()
  {
    $return = array ();

    if ($this->_path)
    {
      if($this->isStartArticle())
        $explode = explode('|', $this->_path.$this->_id.'|');
      else
        $explode = explode('|', $this->_path);

      if (is_array($explode))
      {
        foreach ($explode as $var)
        {
          if ($var != '')
          {
            $return[] = OOCategory :: getCategoryById($var, $this->_clang);
          }
        }
      }
    }

    return $return;
  }
  
  /*
   * Object Function:
   * Checks if $anObj is in the parent tree of the object
   */
  /*public*/ function inParentTree($anObj)
  {
  	$tree = $this->getParentTree();
  	foreach($tree as $treeObj)
  	{
  		if($treeObj == $anObj)
  		{
  			return true;
  		}
  	}
  	return false;
  }

  /**
   *  Accessor Method:
   * returns true if this Article is the Startpage for the category.
   * @deprecated
   */
  /*public*/ function isStartPage()
  {
    return $this->isStartArticle();
  }

  /**
   *  Accessor Method:
   * returns true if this Article is the Startpage for the category.
   */
  /*public*/ function isStartArticle()
  {
    return $this->_startpage;
  }

  /**
   *  Accessor Method:
   * returns true if this Article is the Startpage for the entire site.
   */
  /*public*/ function isSiteStartArticle()
  {
    global $REX;
    return $this->_id == $REX['START_ARTICLE_ID'];
  }

  /**
   *  Accessor Method:
   *  returns  true if this Article is the not found article
   */
  /*public*/ function isNotFoundArticle()
  {
    global $REX;
    return $this->_id == $REX['NOTFOUND_ARTICLE_ID'];
  }

  /*
   * Object Helper Function:
   * Returns a String representation of this object
   * for debugging purposes.
   */
  /*public*/ function toString()
  {
    return $this->_id.', '.$this->_name.', '. ($this->isOnline() ? 'online' : 'offline');
  }
}