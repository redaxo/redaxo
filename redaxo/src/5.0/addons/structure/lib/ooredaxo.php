<?php

/**
 * Object Oriented Framework: Basisklasse für die Strukturkomponenten
 * @package redaxo5
 * @version svn:$Id$
 */

abstract class rex_ooRedaxo
{
  /*
   * these vars get read out
   */
  protected
  $_id = '',
  $_re_id = '',
  $_clang = '',
  $_name = '',
  $_catname = '',
  $_template_id = '',
  $_path = '',
  $_prior = '',
  $_startpage = '',
  $_status = '',
  $_attributes = '',
  $_updatedate = '',
  $_createdate = '',
  $_updateuser = '',
  $_createuser = '';

  /*
   * Constructor
   */
  protected function __construct($params = false, $clang = false)
  {
    if ($params !== false)
    {
      foreach (self :: getClassVars() as $var)
      {
        if(isset($params[$var]))
        {
          $class_var = '_'.$var;
          $this->$class_var = $params[$var];
        }
      }
    }

    if ($clang !== false)
    {
      $this->setClang($clang);
    }
  }

  public function setClang($clang)
  {
    $this->_clang = $clang;
  }

  /**
   * Class Function:
   * Returns Object Value
   *
   * @return string
   */
  public function getValue($value)
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

  /**
   * @param string $value
   * @param array $prefixes
   * @return boolean
   */
  static protected function _hasValue($value, array $prefixes = array())
  {
    static $values = null;

    if(!$values)
    {
      $values = self :: getClassVars();
    }

    if (in_array($value, $values))
    {
      return true;
    }

    foreach($prefixes as $prefix)
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
   *
   * @return array[string]
   */
  static public function getClassVars()
  {
    static $vars = array ();

    if (empty($vars))
    {
      global $REX;

      $vars = array();

      $file = rex_path::cache('articles/'.  $REX['START_ARTICLE_ID'] .'.0.article');
      if(!$REX['REDAXO'] && file_exists($file))
      {
        // Im GetGenerated Modus, die Spaltennamen aus den generated Dateien holen
        if(!isset($REX['ART'][$REX['START_ARTICLE_ID']]))
        {
          $REX['ART'][$REX['START_ARTICLE_ID']] = rex_file::getCache($file);
        }

        // da getClassVars() eine statische Methode ist, können wir hier nicht mit $this->getId() arbeiten!
        $genVars = self::convertGeneratedArray($REX['ART'][$REX['START_ARTICLE_ID']],0);
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

  /**
   * CLASS Function:
   * Converts Genernated Array to OOBase Format Array
   *
   * @return array
   */
  static public function convertGeneratedArray($generatedArray, $clang)
  {
    $rex_ooRedaxoArray['id'] = $generatedArray['article_id'][$clang];
    $rex_ooRedaxoArray['clang'] = $clang;
    foreach ($generatedArray as $key => $var)
    {
      $rex_ooRedaxoArray[$key] = $var[$clang];
    }
    unset ($rex_ooRedaxoArray['_article_id']);
    return $rex_ooRedaxoArray;
  }
  
  /**
   * Array of rex_ooRedaxo instances, key by classname, id and clang
   * @var array[string][int][int]
   */
  private static $instanceCache;
  
  /**
   * Return an rex_ooRedaxo object based on an id.
   * The instance will be cached in an instance-pool and therefore re-used by a later call. 
   * 
   * @param int $id the article id
   * @param int $clang the clang id
   * @throws rexException
   * 
   * @return rex_ooRedaxo A rex_ooRedaxo instance typed to the late-static binding type of the caller
   */
  static protected function getById($id, $clang)
  {
    global $REX;

    $id = (int) $id;

    if($id <= 0)
    {
      return NULL;
    }

    if ($clang === FALSE)
    {
      $clang = $REX['CUR_CLANG'];
    }
    
    // save cache per subclass
    $subclass = get_called_class();
    
    // check if the class was already stored in the instanceCache
    if(isset(self::$instanceCache[$subclass][$id][$clang]))
    {
      return self::$instanceCache[$subclass][$id][$clang];
    }

    $article_path = rex_path::cache('articles/'.$id.'.'.$clang.'.article');
    // generate cache if not exists
    if (!file_exists($article_path))
    {
      rex_article_cache::generateMeta($id, $clang);
    }
    
    // article is valid, if cache exists after generation
    if (file_exists($article_path))
    {
      // load metadata from cache
      $metadata = rex_file::getCache($article_path);
      
      // create object with the loaded metadata
      $impl = new $subclass(self :: convertGeneratedArray($metadata, $clang));

      // put the constructed object into the instance-cache for faster re-use
      self::$instanceCache[$subclass][$id][$clang] = $impl;
      return $impl;
    }

    return NULL;
  }  

  /**
   * Accessor Method:
   * returns the clang of the category
   *
   * @return integer
   */
  public function getClang()
  {
    return $this->_clang;
  }

  /**
   * Object Helper Function:
   * Returns a url for linking to this article
   *
   * @return string
   */
  public function getUrl($params = '', $divider = '&amp;')
  {
    return rex_getUrl($this->getId(), $this->getClang(), $params, $divider);
  }

  /**
   * Accessor Method:
   * returns the id of the article
   *
   * @return integer
   */
  public function getId()
  {
    return $this->_id;
  }

  /**
   * Accessor Method:
   * returns the parent_id of the article
   *
   * @return integer
   */
  public function getParentId()
  {
    return $this->_re_id;
  }
  
  /**
   * Accessor Method:
   * returns the path of the category/article
   *
   * @return string
   */
  public abstract function getPath();

  /**
   * Accessor Method:
   * returns the path ids of the category/article as an array
   *
   * @return array[int]
   */
  public function getPathAsArray()
  {
    $path = explode('|', $this->getPath());
    return array_values(array_map('intval', array_filter($path)));
  }
  
  /**
   * Object Function:
   * Returns the parent category
   *
   * @return rex_ooRedaxo
   */
  public abstract function getParent($clang = false);

  /**
   * Accessor Method:
   * returns the name of the article
   *
   * @return string
   */
  public function getName()
  {
    return $this->_name;
  }

  /**
   * Accessor Method:
   * returns the article priority
   *
   * @return integer
   */
  public function getPriority()
  {
    return $this->_prior;
  }

  /**
   * Accessor Method:
   * returns the last update user
   *
   * @return string
   */
  public function getUpdateUser()
  {
    return $this->_updateuser;
  }

  /**
   * Accessor Method:
   * returns the last update date
   *
   * @return integer
   */
  public function getUpdateDate($format = null)
  {
    return self :: _getDate($this->_updatedate, $format);
  }

  /**
   * Accessor Method:
   * returns the creator
   *
   * @return string
   */
  public function getCreateUser()
  {
    return $this->_createuser;
  }

  /**
   * Accessor Method:
   * returns the creation date
   *
   * @return integer
   */
  public function getCreateDate($format = null)
  {
    return self :: _getDate($this->_createdate, $format);
  }

  /**
   * Accessor Method:
   * returns true if article is online.
   *
   * @return boolean
   */
  public function isOnline()
  {
    return $this->_status == 1;
  }

  /**
   * Accessor Method:
   * returns true if article is offline.
   *
   * @return boolean
   */
  public function isOffline()
  {
    return $this->_status == 0;
  }

  /**
   * Accessor Method:
   * returns the template id
   *
   * @return integer
   */
  public function getTemplateId()
  {
    return $this->_template_id;
  }

  /**
   * Accessor Method:
   * returns true if article has a template.
   *
   * @return boolean
   */
  public function hasTemplate()
  {
    return $this->_template_id > 0;
  }

  /**
   * Accessor Method:
   * Returns a link to this article
   *
   * @param [$params] Parameter für den Link
   * @param [$attributes] array Attribute die dem Link hinzugefügt werden sollen. Default: null
   * @param [$sorround_tag] string HTML-Tag-Name mit dem der Link umgeben werden soll, z.b. 'li', 'div'. Default: null
   * @param [sorround_attributes] array Attribute die Umgebenden-Element hinzugefügt werden sollen. Default: null
   *
   * @return string
   */
  public function toLink($params = '', $attributes = null, $sorround_tag = null, $sorround_attributes = null)
  {
    $name = htmlspecialchars($this->getName());
    $link = '<a href="'.$this->getUrl($params).'"'.$this->_toAttributeString($attributes).' title="'.$name.'">'.$name.'</a>';

    if ($sorround_tag !== null && is_string($sorround_tag))
    {
      $link = '<'.$sorround_tag.$this->_toAttributeString($sorround_attributes).'>'.$link.'</'.$sorround_tag.'>';
    }

    return $link;
  }

  /**
   * @param array $attributes
   *
   * @return string
   */
  protected function _toAttributeString($attributes)
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

  /**
   * Object Function:
   * Get an array of all parentCategories.
   * Returns an array of rex_ooRedaxo objects.
   *
   * @return array[rex_ooCategory]
   */
  public function getParentTree()
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
            $return[] = rex_ooCategory :: getCategoryById($var, $this->_clang);
          }
        }
      }
    }

    return $return;
  }

  /**
   * Object Function:
   * Checks if $anObj is in the parent tree of the object
   *
   * @return boolean
   */
  public function inParentTree($anObj)
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
   *
   * @return boolean
   */
  public function isStartPage()
  {
    return $this->isStartArticle();
  }

  /**
   *  Accessor Method:
   * returns true if this Article is the Startpage for the category.
   *
   * @return boolean
   */
  public function isStartArticle()
  {
    return $this->_startpage;
  }

  /**
   *  Accessor Method:
   * returns true if this Article is the Startpage for the entire site.
   *
   * @return boolean
   */
  public function isSiteStartArticle()
  {
    global $REX;
    return $this->_id == $REX['START_ARTICLE_ID'];
  }

  /**
   *  Accessor Method:
   *  returns  true if this Article is the not found article
   *
   *  @return boolean
   */
  public function isNotFoundArticle()
  {
    global $REX;
    return $this->_id == $REX['NOTFOUND_ARTICLE_ID'];
  }

  /**
   * Object Helper Function:
   * Returns a String representation of this object
   * for debugging purposes.
   *
   * @return string
   */
  public function toString()
  {
    return $this->_id.', '.$this->_name.', '. ($this->isOnline() ? 'online' : 'offline');
  }

  /**
   * Formats a datestamp with the given format.
   *
   * If format is <code>null</code> the datestamp is returned.
   *
   * If format is <code>''</code> the datestamp is formated
   * with the default <code>dateformat</code> (lang-files).
   *
   * @access public
   * @static
   */
  static protected function _getDate($date, $format = null)
  {
    if ($format !== null)
    {
      if ($format == '')
      {
        $format = rex_i18n::msg('dateformat');
      }
      return strftime($format, $date);
    }
    return $date;
  }
}