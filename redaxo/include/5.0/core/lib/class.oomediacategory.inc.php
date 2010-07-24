<?php


/**
 * Object Oriented Framework: Bildet eine Kategorie im Medienpool ab
 * @package redaxo4
 * @version svn:$Id$
 */

class OOMediaCategory
{
  // id
  var $_id = "";
  // re_id
  var $_parent_id = "";

  // name
  var $_name = "";
  // path
  var $_path = "";

  // createdate
  var $_createdate = "";
  // updatedate
  var $_updatedate = "";

  // createuser
  var $_createuser = "";
  // updateuser
  var $_updateuser = "";

  // child categories
  var $_children = "";
  // files (media)
  var $_files = "";

  /**
  * @access protected
  */
  function OOMediaCategory($id = null)
  {
    $this->getCategoryById($id);
  }

  /**
   * @access public
   */
  function getCategoryById($id)
  {
    global $REX;
    
    $id = (int) $id;
    if (!is_numeric($id))
      return null;

    $cat_path = $REX['INCLUDE_PATH'].'/generated/files/'.$id.'.mcat';
    if (!file_exists($cat_path))
		{
			require_once ($REX['INCLUDE_PATH'].'/functions/function_rex_generate.inc.php');
    	rex_generateMediaCategory($id);
		}

    if (file_exists($cat_path))
    {
      require_once ($cat_path);

      $cat = new OOMediaCategory();
  
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
  function getRootCategories()
  {
    return OOMediaCategory :: getChildrenById(0);
  }
  
  /**
   * @access public
   */
  function getChildrenById($id)
  {
    global $REX;
    
    $id = (int) $id;

    if(!is_int($id))
      return array();
      
    $catlist = array();
  
    $catlist_path = $REX['INCLUDE_PATH'].'/generated/files/'.$id.'.mclist';
    if (!file_exists($catlist_path))
		{
			require_once ($REX['INCLUDE_PATH'].'/functions/function_rex_generate.inc.php');
    	rex_generateMediaCategoryList($id);
		}

    if (file_exists($catlist_path))
    {
      require_once ($catlist_path);
      
      if (isset($REX['MEDIA']['RE_CAT_ID'][$id]) && is_array($REX['MEDIA']['RE_CAT_ID'][$id])) 
      {
        foreach($REX['MEDIA']['RE_CAT_ID'][$id] as $cat_id)
          $catlist[] = OOMediaCategory :: getCategoryById($cat_id);
      }
    }
    
    return $catlist;
  }

  /**
   * @access public
   */
  function toString()
  {
    return 'OOMediaCategory, "' . $this->getId() . '", "' . $this->getName() . '"' . "<br/>\n";
  }

  /**
   * @access public
   */
  function getId()
  {
    return $this->_id;
  }

  /**
   * @access public
   */
  function getName()
  {
    return $this->_name;
  }

  /**
   * @access public
   */
  function getPath()
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
  
  /**
   * @access public
   */
  function getUpdateUser()
  {
    return $this->_updateuser;
  }

  /**
   * @access public
   */
  function getUpdateDate()
  {
    return $this->_updatedate;
  }

  /**
   * @access public
   */
  function getCreateUser()
  {
    return $this->_createuser;
  }

  /**
   * @access public
   */
  function getCreateDate()
  {
    return $this->_createdate;
  }

  /**
   * @access public
   */
  function getParentId()
  {
    return $this->_parent_id;
  }

  /**
   * @access public
   */
  function getParent()
  {
    return OOMediaCategory :: getCategoryById($this->getParentId());
  }
  
  /**
   * @access public
   * Get an array of all parentCategories.
   * Returns an array of OORedaxo objects sorted by $prior.
   * 
   */
  function getParentTree()
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
            $tree[] = OOMediaCategory :: getCategoryById($var);
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
  function inParentTree($anObj)
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
  function getChildren()
  {
    global $REX;
    
    if ($this->_children === null)
    {
      $this->_children = OOMediaCategory :: getChildrenById($this->getId());
    }

    return $this->_children;
  }

  /**
   * @access public
   */
  function countChildren()
  {
    return count($this->getChildren());
  }

  /**
   * @access public
   */
  function getMedia()
  {
    global $REX;
    
    if ($this->_files === null)
    {
      $this->_files = array();
      $id = $this->getId();
    
      $list_path = $REX['INCLUDE_PATH'].'/generated/files/'.$id.'.mlist';
      if (!file_exists($list_path))
  		{
  			require_once ($REX['INCLUDE_PATH'].'/functions/function_rex_generate.inc.php');
      	rex_generateMediaList($id);
  		}
  
      if (file_exists($list_path))
      {
        require_once ($list_path);
        
        if (isset($REX['MEDIA']['MEDIA_CAT_ID'][$id]) && is_array($REX['MEDIA']['MEDIA_CAT_ID'][$id])) 
        {
          foreach($REX['MEDIA']['MEDIA_CAT_ID'][$id] as $filename)
            $this->_files[] = & OOMedia :: getMediaByFileName($filename);
        }
      }
    }

    return $this->_files;
  }

  /**
   * @access public
   */
  function countMedia()
  {
    return count($this->getFiles());
  }

  /**
   * @access public
   */
  function isHidden()
  {
    return $this->_hide;
  }

  /**
   * @access public
   */
  function isRootCategory()
  {
    return $this->hasParent() === false;
  }

  /**
   * @access public
   */
  function isParent($mediaCat)
  {
    if (is_int($mediaCat))
    {
      return $mediaCat == $this->getParentId();
    }
    elseif (OOMediaCategory :: isValid($mediaCat))
    {
      return $this->getParentId() == $mediaCat->getId();
    }
    return null;
  }

  /**
   * @access public
   */
  function isValid($mediaCat)
  {
    return is_object($mediaCat) && is_a($mediaCat, 'oomediacategory');
  }

  /**
   * @access public
   */
  function hasParent()
  {
    return $this->getParentId() != 0;
  }

  /**
   * @access public
   */
  function hasChildren()
  {
    return count($this->getChildren()) > 0;
  }

  /**
   * @access public
   */
  function hasMedia()
  {
    return count($this->getMedia()) > 0;
  }
  
  /**
   * @access protected
   */
  function _getTableName()
  {
    global $REX;
    return $REX['TABLE_PREFIX'] . 'file_category';
  }

  /**
   * @access public
   * @return Returns <code>true</code> on success or <code>false</code> on error
   */
  function save()
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
  function delete($recurse = false)
  {
    // Rekursiv löschen?
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
    
    // Alle Dateien löschen
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
   * @deprecated 20.02.2010
   * Stattdessen getCategoryById() nutzen
   */
  function getCategoryByName($name)
  { 
    $query = 'SELECT id FROM ' . OOMediaCategory :: _getTableName() . ' WHERE name = "' . $name . '"';
    $sql = new rex_sql();
    //$sql->debugsql = true;
    $result = $sql->getArray($query);

    $media = array ();
    if (is_array($result))
    {
      foreach ($result as $line)
      {
        $media[] = OOMediaCategory :: getCategoryById($line['id']);
      }
    }

    return $media;
  }

  /**
   * @access public
   * @deprecated 4.2 - 17.05.2008
   */
  function countFiles()
  {
    return $this->countMedia();
  }

  /**
   * @access public
   * @deprecated 4.2 - 17.05.2008
   */
  function hasFiles()
  {
    return $this->hasMedia();
  }

  /**
   * @access public
   * @deprecated 4.2 - 17.05.2008
   */
  function getFiles()
  {
    return $this->getMedia();
  }
}