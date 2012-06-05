<?php

/**
 * Object Oriented Framework: Basisklasse für die Strukturkomponenten
 * @package redaxo5
 */

abstract class rex_structure_element
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
    if ($params !== false) {
      foreach (self :: getClassVars() as $var) {
        if (isset($params[$var])) {
          $class_var = '_' . $var;
          $this->$class_var = $params[$var];
        }
      }
    }

    if ($clang !== false) {
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
    foreach (array('_', 'art_', 'cat_') as $prefix) {
      $val = $prefix . $value;
      if (isset($this->$val)) {
        return $this->$val;
      }
    }
    return null;
  }

  /**
   * @param string $value
   * @param array  $prefixes
   * @return boolean
   */
  static protected function _hasValue($value, array $prefixes = array())
  {
    static $values = null;

    if (!$values) {
      $values = self :: getClassVars();
    }

    if (in_array($value, $values)) {
      return true;
    }

    foreach ($prefixes as $prefix) {
      if (in_array($prefix . $value, $values)) {
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

    if (empty($vars)) {
      $vars = array();

      $startId = rex::getProperty('start_article_id');
      $file = rex_path::addonCache('structure',  $startId . '.0.article');
      if (!rex::isBackend() && file_exists($file)) {
        // da getClassVars() eine statische Methode ist, können wir hier nicht mit $this->getId() arbeiten!
        $genVars = self::convertGeneratedArray(rex_file::getCache($file), 0);
        unset($genVars['article_id']);
        unset($genVars['last_update_stamp']);
        foreach ($genVars as $name => $value) {
          $vars[] = $name;
        }
      } else {
        // Im Backend die Spalten aus der DB auslesen / via EP holen
        $sql = rex_sql::factory();
        $sql->setQuery('SELECT * FROM ' . rex::getTablePrefix() . 'article LIMIT 0');
        foreach ($sql->getFieldnames() as $field) {
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
    $rex_structure_elementArray['id'] = $generatedArray['article_id'][$clang];
    $rex_structure_elementArray['clang'] = $clang;
    foreach ($generatedArray as $key => $var) {
      $rex_structure_elementArray[$key] = $var[$clang];
    }
    unset ($rex_structure_elementArray['_article_id']);
    return $rex_structure_elementArray;
  }

  /**
   * Array of rex_structure_element instances, keyed by classname, id and clang
   * @var array[string][int][int]
   */
  static private $instanceCache = array();

  /**
   * Return an rex_structure_element object based on an id.
   * The instance will be cached in an instance-pool and therefore re-used by a later call.
   *
   * @param int $id    the article id
   * @param int $clang the clang id
   * @throws rex_exception
   *
   * @return rex_structure_element A rex_structure_element instance typed to the late-static binding type of the caller
   */
  static protected function getById($id, $clang)
  {
    $id = (int) $id;

    if ($id <= 0) {
      return null;
    }

    if ($clang === false) {
      $clang = rex_clang::getCurrentId();
    }

    // save cache per subclass
    $subclass = get_called_class();

    // check if the class was already stored in the instanceCache
    if (isset(self::$instanceCache[$subclass][$id][$clang])) {
      return self::$instanceCache[$subclass][$id][$clang];
    }

    $article_path = rex_path::addonCache('structure', $id . '.' . $clang . '.article');
    // generate cache if not exists
    if (!file_exists($article_path)) {
      rex_article_cache::generateMeta($id, $clang);
    }

    // article is valid, if cache exists after generation
    if (file_exists($article_path)) {
      // load metadata from cache
      $metadata = rex_file::getCache($article_path);

      // create object with the loaded metadata
      $impl = new $subclass(self :: convertGeneratedArray($metadata, $clang));

      // put the constructed object into the instance-cache for faster re-use
      self::$instanceCache[$subclass][$id][$clang] = $impl;
      return $impl;
    }

    return null;
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
  abstract public function getPath();

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
   * @return rex_structure_element
   */
  abstract public function getParent($clang = false);

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
    $link = '<a href="' . $this->getUrl($params) . '"' . $this->_toAttributeString($attributes) . ' title="' . $name . '">' . $name . '</a>';

    if ($sorround_tag !== null && is_string($sorround_tag)) {
      $link = '<' . $sorround_tag . $this->_toAttributeString($sorround_attributes) . '>' . $link . '</' . $sorround_tag . '>';
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

    if ($attributes !== null && is_array($attributes)) {
      foreach ($attributes as $name => $value) {
        $attr .= ' ' . $name . '="' . $value . '"';
      }
    }

    return $attr;
  }

  /**
   * Object Function:
   * Get an array of all parentCategories.
   * Returns an array of rex_structure_element objects.
   *
   * @return array[rex_category]
   */
  public function getParentTree()
  {
    $return = array ();

    if ($this->_path) {
      if ($this->isStartArticle())
      $explode = explode('|', $this->_path . $this->_id . '|');
      else
      $explode = explode('|', $this->_path);

      if (is_array($explode)) {
        foreach ($explode as $var) {
          if ($var != '') {
            $return[] = rex_category :: getCategoryById($var, $this->_clang);
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
    foreach ($tree as $treeObj) {
      if ($treeObj == $anObj) {
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
    return $this->_id == rex::getProperty('start_article_id');
  }

  /**
   *  Accessor Method:
   *  returns  true if this Article is the not found article
   *
   * @return boolean
   */
  public function isNotFoundArticle()
  {
    return $this->_id == rex::getProperty('notfound_article_id');
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
    return $this->_id . ', ' . $this->_name . ', ' . ($this->isOnline() ? 'online' : 'offline');
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
    if ($format !== null) {
      if ($format == '') {
        $format = rex_i18n::msg('dateformat');
      }
      return strftime($format, $date);
    }
    return $date;
  }
}
