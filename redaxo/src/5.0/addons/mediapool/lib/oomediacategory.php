<?php


/**
 * Object Oriented Framework: Bildet eine Kategorie im Medienpool ab
 * @package redaxo4
 * @version svn:$Id$
 */

class rex_ooMediaCategory
{
  // id
  private $_id = "";
  // re_id
  private $_parent_id = "";

  // name
  private $_name = "";
  // path
  private $_path = "";

  // createdate
  private $_createdate = "";
  // updatedate
  private $_updatedate = "";

  // createuser
  private $_createuser = "";
  // updateuser
  private $_updateuser = "";

  // child categories
  private $_children = "";
  // files (media)
  private $_files = "";

  /**
  * @access protected
  */
  protected function __construct($id = null)
  {
    $this->getCategoryById($id);
  }

  /**
   * @access public
   */
  static public function getCategoryById($id)
  {
    global $REX;

    $id = (int) $id;
    if (!is_numeric($id))
      return null;

    $cat_path = rex_path::generate('files/'.$id.'.mcat');
    if (!file_exists($cat_path))
		{
    	rex_generateMediaCategory($id);
		}

    if (file_exists($cat_path))
    {
      require_once ($cat_path);

      $cat = new rex_ooMediaCategory();

      $cat->_id = $REX['MEDIA']['CAT_ID'][$id]['id'];
      $cat->_parent_id = $REX['MEDIA']['CAT_ID'][$id]['re_id'];

      $cat->_name = $REX['MEDIA']['CAT_ID'][$id]['name'];
      $cat->_path = $REX['MEDIA']['CAT_ID'][$id]['path'];

      $cat->_createdate = $REX['MEDIA']['CAT_ID'][$id]['createdate'];
      $cat->_updatedate = $REX['MEDIA']['CAT_ID'][$id]['updatedate'];

      $cat->_createuser = $REX['MEDIA']['CAT_ID'][$id]['createuser'];
      $cat->_updateuser = $REX['MEDIA']['CAT_ID'][$id]['updateuser'];

      $cat->_children = null;
      $cat->_files = null;

      return $cat;
    }

    return null;
  }

  /**
   * @access public
   */
  static public function getRootCategories()
  {
    return self :: getChildrenById(0);
  }

  /**
   * @access public
   */
  static public function getChildrenById($id)
  {
    global $REX;

    $id = (int) $id;

    if(!is_int($id))
      return array();

    $catlist = array();

    $catlist_path = rex_path::generate('files/'.$id.'.mclist');
    if (!file_exists($catlist_path))
		{
    	rex_generateMediaCategoryList($id);
		}

    if (file_exists($catlist_path))
    {
      require_once ($catlist_path);

      if (isset($REX['MEDIA']['RE_CAT_ID'][$id]) && is_array($REX['MEDIA']['RE_CAT_ID'][$id]))
      {
        foreach($REX['MEDIA']['RE_CAT_ID'][$id] as $cat_id)
          $catlist[] = self :: getCategoryById($cat_id);
      }
    }

    return $catlist;
  }

  /**
   * @access public
   */
  public function toString()
  {
    return 'rex_ooMediaCategory, "' . $this->getId() . '", "' . $this->getName() . '"' . "<br/>\n";
  }

  /**
   * @access public
   */
  public function getId()
  {
    return $this->_id;
  }

  /**
   * @access public
   */
  public function getName()
  {
    return $this->_name;
  }

  /**
   * @access public
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

  /**
   * @access public
   */
  public function getUpdateUser()
  {
    return $this->_updateuser;
  }

  /**
   * @access public
   */
  public function getUpdateDate()
  {
    return $this->_updatedate;
  }

  /**
   * @access public
   */
  public function getCreateUser()
  {
    return $this->_createuser;
  }

  /**
   * @access public
   */
  public function getCreateDate()
  {
    return $this->_createdate;
  }

  /**
   * @access public
   */
  public function getParentId()
  {
    return $this->_parent_id;
  }

  /**
   * @access public
   */
  public function getParent()
  {
    return self :: getCategoryById($this->getParentId());
  }

  /**
   * @access public
   * Get an array of all parentCategories.
   * Returns an array of rex_ooRedaxo objects sorted by $prior.
   *
   */
  public function getParentTree()
  {
    $tree = array();
    if($this->_path)
    {
      $explode = explode('|', $this->_path);
      if(is_array($explode))
      {
        foreach($explode as $var)
        {
          if($var != '')
          {
            $tree[] = self :: getCategoryById($var);
          }
        }
      }
    }
    return $tree;
  }

  /*
   * Object Function:
   * Checks if $anObj is in the parent tree of the object
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
   * @access public
   */
  public function getChildren()
  {
    global $REX;

    if ($this->_children === null)
    {
      $this->_children = rex_ooMediaCategory :: getChildrenById($this->getId());
    }

    return $this->_children;
  }

  /**
   * @access public
   */
  public function countChildren()
  {
    return count($this->getChildren());
  }

  /**
   * @access public
   */
  public function getMedia()
  {
    global $REX;

    if ($this->_files === null)
    {
      $this->_files = array();
      $id = $this->getId();

      $list_path = rex_path::generate('files/'.$id.'.mlist');
      if (!file_exists($list_path))
  		{
      	rex_generateMediaList($id);
  		}

      if (file_exists($list_path))
      {
        require_once ($list_path);

        if (isset($REX['MEDIA']['MEDIA_CAT_ID'][$id]) && is_array($REX['MEDIA']['MEDIA_CAT_ID'][$id]))
        {
          foreach($REX['MEDIA']['MEDIA_CAT_ID'][$id] as $filename)
            $this->_files[] = rex_ooMedia :: getMediaByFileName($filename);
        }
      }
    }

    return $this->_files;
  }

  /**
   * @access public
   */
  public function countMedia()
  {
    return count($this->getFiles());
  }

  /**
   * @access public
   */
  public function isHidden()
  {
    return $this->_hide;
  }

  /**
   * @access public
   */
  public function isRootCategory()
  {
    return $this->hasParent() === false;
  }

  /**
   * @access public
   */
  public function isParent($mediaCat)
  {
    if (is_int($mediaCat))
    {
      return $mediaCat == $this->getParentId();
    }
    elseif (rex_ooMediaCategory :: isValid($mediaCat))
    {
      return $this->getParentId() == $mediaCat->getId();
    }
    return null;
  }

  /**
   * @access public
   */
  static public function isValid($mediaCat)
  {
    return is_object($mediaCat) && is_a($mediaCat, 'rex_ooMediacategory');
  }

  /**
   * @access public
   */
  public function hasParent()
  {
    return $this->getParentId() != 0;
  }

  /**
   * @access public
   */
  public function hasChildren()
  {
    return count($this->getChildren()) > 0;
  }

  /**
   * @access public
   */
  public function hasMedia()
  {
    return count($this->getMedia()) > 0;
  }

  /**
   * @access public
   */
  static public function _getTableName()
  {
    global $REX;
    return $REX['TABLE_PREFIX'] . 'file_category';
  }

  /**
   * @access public
   * @return Returns <code>true</code> on success or <code>false</code> on error
   */
  public function save()
  {
    $sql = rex_sql::factory();
    $sql->setTable($this->_getTableName());
    $sql->setValue('re_id', $this->getParentId());
    $sql->setValue('name', $this->getName());
    $sql->setValue('path', $this->getPath());
    $sql->setValue('hide', $this->isHidden());

    if ($this->getId() !== null)
    {
      $sql->addGlobalUpdateFields();
      $sql->setWhere('id=' . $this->getId() . ' LIMIT 1');
      $success = $sql->update();
      if ($success)
        rex_deleteCacheMediaCategory($this->getId());
      return $success;
    }
    else
    {
      $sql->addGlobalCreateFields();
      $success = $sql->insert();
      if ($success)
        rex_deleteCacheMediaCategoryList($this->getParentId());
      return $success;
    }
  }

  /**
   * @access public
   * @return Returns <code>true</code> on success or <code>false</code> on error
   */
  public function delete($recurse = false)
  {
    // Rekursiv l�schen?
    if(!$recurse && $this->hasChildren())
    {
      return false;
    }

    if ($recurse)
    {
      $childs = $this->getChildren();
      foreach ($childs as $child)
      {
        if(!$child->delete($recurse)) return false;
      }
    }

    // Alle Dateien l�schen
    if ($this->hasMedia())
    {
      $files = $this->getMedia();
      foreach ($files as $file)
      {
        if(!$file->delete()) return false;
      }
    }

    $qry = 'DELETE FROM ' . $this->_getTableName() . ' WHERE id = ' . $this->getId() . ' LIMIT 1';
    $sql = rex_sql::factory();
    // $sql->debugsql = true;
    $sql->setQuery($qry);

    rex_deleteCacheMediaCategory($this->getId());
    rex_deleteCacheMediaList($this->getId());

    return !$sql->hasError() || $sql->getRows() != 1;
  }

  /**
   * @access public
   * @deprecated 4.2 - 17.05.2008
   */
  public function countFiles()
  {
    return $this->countMedia();
  }

  /**
   * @access public
   * @deprecated 4.2 - 17.05.2008
   */
  public function hasFiles()
  {
    return $this->hasMedia();
  }

  /**
   * @access public
   * @deprecated 4.2 - 17.05.2008
   */
  public function getFiles()
  {
    return $this->getMedia();
  }
}