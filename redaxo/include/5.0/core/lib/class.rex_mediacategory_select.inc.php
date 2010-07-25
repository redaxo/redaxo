<?php

/**
 * Klasse zur Erstellung eines HTML-Pulldown-Menues (Select-Box)
 *
 * @package redaxo4
 * @version svn:$Id$
 */

################ Class Select
class rex_select
{
	var $attributes;
  var $options;
  var $option_selected;
  
  ################ Konstruktor
  /*public*/ function rex_select()
  {
    $this->init();
  }

  ################ init
  /*public*/ function init()
  {
    $this->attributes = array();
    $this->resetSelected();
    $this->setName('standard');
    $this->setSize('5');
    $this->setMultiple(false);
    $this->setDisabled(false);
  }

  /*public*/ function setAttribute($name, $value)
  {
  	$this->attributes[$name] = $value;
  }

  /*public*/ function delAttribute($name)
  {
  	if($this->hasAttribute($name))
  	{
  		unset($this->attributes[$name]);
  		return true;
  	}
  	return false;
  }

  /*public*/ function hasAttribute($name)
  {
  	return isset($this->attributes[$name]);
  }

  /*public*/ function getAttribute($name, $default = '')
  {
  	if($this->hasAttribute($name))
  	{
	  	return $this->attributes[$name];
  	}
  	return $default;
  }

  ############### multiple felder ?
  /*public*/ function setMultiple($multiple = true)
  {
    if($multiple)
      $this->setAttribute('multiple', 'multiple');
    else
      $this->delAttribute('multiple');
  }
  
  ############### disabled ?
  /*public*/ function setDisabled($disabled = true)
  {
    if($disabled)
      $this->setAttribute('disabled', 'disabled');
    else
      $this->delAttribute('disabled');
  }
  
  ################ select name
  /*public*/ function setName($name)
  {
  	$this->setAttribute('name', $name);
  }

  ################ select id
  /*public*/ function setId($id)
  {
  	$this->setAttribute('id', $id);
  }

  /**
  * select style
  * Es ist moeglich sowohl eine Styleklasse als auch einen Style zu uebergeben.
  *
  * Aufrufbeispiel:
  * $sel_media->setStyle('class="inp100"');
  * und/oder
  * $sel_media->setStyle("width:150px;");
  */
  /*public*/ function setStyle($style)
  {
    if (strpos($style, 'class=') !== false)
    {
    	if(preg_match('/class=["\']?([^"\']*)["\']?/i', $style, $matches))
    	{
	    	$this->setAttribute('class', $matches[1]);
    	}
    }
    else
    {
    	$this->setAttribute('style', $style);
    }
  }

  ################ select size
  /*public*/ function setSize($size)
  {
  	$this->setAttribute('size', $size);
  }

  ################ selected feld - option value uebergeben
  /*public*/ function setSelected($selected)
  {
  	if(is_array($selected))
  	{
  		foreach($selected as $sectvalue)
  		{
  			$this->setSelected($sectvalue);
  		}
  	}
  	else
  	{
	    $this->option_selected[] = htmlspecialchars($selected);
  	}
  }

  /*public*/ function resetSelected()
  {
    $this->option_selected = array ();
  }

  ################ optionen hinzufuegen
  /**
   * Fügt eine Option hinzu
   */
  /*public*/ function addOption($name, $value, $id = 0, $re_id = 0, $attributes = array())
  {
    $this->options[$re_id][] = array ($name, $value, $id, $attributes);
  }

  /**
   * Fügt ein Array von Optionen hinzu, dass eine mehrdimensionale Struktur hat.
   *
   * Dim   Wert
   * 0.    Name
   * 1.    Value
   * 2.    Id
   * 3.    Re_Id
   * 4.    Selected
   * 5.    Attributes
   */
  /*public*/ function addOptions($options, $useOnlyValues = false)
  {
    if(is_array($options) && count($options)>0)
    {
      // Hier vorher auf is_array abfragen, da bei Strings auch die Syntax mit [] funktioniert
      // $ab = "hallo"; $ab[2] -> "l"
			$grouped = isset($options[0]) && is_array($options[0]) && isset ($options[0][2]) && isset ($options[0][3]);
      foreach ($options as $key => $option)
      {
      	$option = (array) $option;
      	$attributes = array();
      	if (isset($option[5]) && is_array($option[5]))
      	  $attributes = $option[5];
        if ($grouped)
        {
          $this->addOption($option[0], $option[1], $option[2], $option[3], $attributes);
          if(isset($option[4]) && $option[4])
          {
          	$this->setSelected($option[1]);
          }
        }
        else
        {
          if($useOnlyValues)
          {
            $this->addOption($option[0], $option[0]);
          }
          else
          {
            if(!isset($option[1]))
              $option[1] = $key;

            $this->addOption($option[0], $option[1]);
          }
        }
      }
    }
  }

  /**
   * Fügt ein Array von Optionen hinzu, dass eine Key/Value Struktur hat.
   * Wenn $use_keys mit false, werden die Array-Keys mit den Array-Values überschrieben
   */
  /*public*/ function addArrayOptions($options, $use_keys = true)
  {
  	foreach($options as $key => $value)
  	{
      if(!$use_keys)
        $key = $value;

      $this->addOption($value, $key);
  	}
  }

  /**
   * Fügt Optionen anhand der Übergeben SQL-Select-Abfrage hinzu.
   */
  /*public*/ function addSqlOptions($qry)
  {
    $sql = rex_sql::factory();
    $this->addOptions($sql->getArray($qry, MYSQL_NUM));
  }

  /**
   * Fügt Optionen anhand der Übergeben DBSQL-Select-Abfrage hinzu.
   */
  /*public*/ function addDBSqlOptions($qry)
  {
    $sql = rex_sql::factory();
    $this->addOptions($sql->getDBArray($qry, MYSQL_NUM));
  }

  ############### show select
  /*public*/ function get()
  {
  	$attr = '';
  	foreach($this->attributes as $name => $value)
  	{
  		$attr .= ' '. $name .'="'. $value .'"';
  	}
  	
    $ausgabe = "\n";
		$ausgabe .= '<select'.$attr.'>'."\n";

    if (is_array($this->options))
      $ausgabe .= $this->_outGroup(0);

    $ausgabe .= '</select>'. "\n";
    return $ausgabe;
  }

  ############### show select
  /*public*/ function show()
  {
  	echo $this->get();
  }

  /*private*/ function _outGroup($re_id, $level = 0)
  {

    if ($level > 100)
    {
      // nur mal so zu sicherheit .. man weiss nie ;)
      echo "select->_outGroup overflow ($groupname)";
      exit;
    }

    $ausgabe = '';
    $group = $this->_getGroup($re_id);
    foreach ($group as $option)
    {
      $name = $option[0];
      $value = $option[1];
      $id = $option[2];
      $attributes = array();
      if (isset($option[3]) && is_array($option[3]))
        $attributes = $option[3];
      $ausgabe .= $this->_outOption($name, $value, $level, $attributes);

      $subgroup = $this->_getGroup($id, true);
      if ($subgroup !== false)
      {
        $ausgabe .= $this->_outGroup($id, $level +1);
      }
    }
    return $ausgabe;
  }

  /*private*/ function _outOption($name, $value, $level = 0, $attributes = array())
  {
    $name = htmlspecialchars($name);
    $value = htmlspecialchars($value);

    $bsps = '';
    if ($level > 0)
      $bsps = str_repeat('&nbsp;&nbsp;&nbsp;', $level);
    
    if ($this->option_selected !== null && in_array($value, $this->option_selected))
      $attributes['selected'] = 'selected';
    
    $attr = '';
  	foreach($attributes as $n => $v)
  	{
  		$attr .= ' '. $n .'="'. $v .'"';
  	}

    return '    <option value="'.$value.'"'.$attr.'>'.$bsps.$name.'</option>'."\n";
  }

  /*private*/ function _getGroup($re_id, $ignore_main_group = false)
  {

    if ($ignore_main_group && $re_id == 0)
    {
      return false;
    }

    foreach ($this->options as $gname => $group)
    {
      if ($gname == $re_id)
      {
        return $group;
      }
    }

    return false;
  }
}

################ Class Kategorie Select
class rex_category_select extends rex_select
{
  /*private*/ var $ignore_offlines;
  /*private*/ var $clang;
  /*private*/ var $check_perms;
  /*private*/ var $rootId;

  /*public*/ function rex_category_select($ignore_offlines = false, $clang = false, $check_perms = true, $add_homepage = true)
  {
    $this->ignore_offlines = $ignore_offlines;
    $this->clang = $clang;
    $this->check_perms = $check_perms;
    $this->add_homepage = $add_homepage;
    $this->rootId = null;

    parent::rex_select();
  }

  /**
   * Kategorie-Id oder ein Array von Kategorie-Ids als Wurzelelemente der Select-Box.
   * 
   * @param $rootId mixed Kategorie-Id oder Array von Kategorie-Ids zur Identifikation der Wurzelelemente. 
   */
  /*public*/ function setRootId($rootId)
  {
    $this->rootId = $rootId;
  }
  
  /*protected*/ function addCatOptions()
  {
    global $REX;

    if($this->add_homepage)
      $this->addOption('Homepage', 0);
      
    if($this->rootId !== null)
    {
      if(is_array($this->rootId))
      {
        foreach($this->rootId as $rootId)
        {
          if($rootCat = OOCategory::getCategoryById($rootId, $this->clang))
          {
            $this->addCatOption($rootCat, 0);
          }
        }
      }
      else
      {
        if($rootCat = OOCategory::getCategoryById($this->rootId, $this->clang))
        {
          $this->addCatOption($rootCat, 0);
        }
      }
    }
    else
    {
      if(!$this->check_perms || $REX['USER']->isAdmin() || $REX['USER']->hasPerm('csw[0]'))
      {
        if($rootCats = OOCategory :: getRootCategories($this->ignore_offlines, $this->clang))
        {
          foreach($rootCats as $rootCat)
          {
            $this->addCatOption($rootCat);
          }
        }
      }
      elseif($REX['USER']->hasMountpoints())
      {
        $mountpoints = $REX['USER']->getMountpoints();
        foreach($mountpoints as $id)
        {
          $cat = OOCategory::getCategoryById($id, $this->clang);
          if ($cat && !$REX['USER']->hasCategoryPerm($cat->getParentId()))
            $this->addCatOption($cat, 0);
        }
      }
    }
  }
  
  /*protected*/ function addCatOption(/*OOCategory*/ $cat, $group = null)
  {
    global $REX;

    if(!$this->check_perms ||
        $this->check_perms && $REX['USER']->hasCategoryPerm($cat->getId(),FALSE))
    {
      $cid = $cat->getId();
      $cname = $cat->getName();
      
      if($REX['USER']->hasPerm('advancedMode[]'))
        $cname .= ' ['. $cid .']';
      
      if($group === null)
        $group = $cat->getParentId();
      
      $this->addOption($cname, $cid, $cid, $group);
      $childs = $cat->getChildren($this->ignore_offlines, $this->clang);
      if (is_array($childs))
      {
        foreach ($childs as $child)
        {
          $this->addCatOption($child);
        }
      }
    }
  }

  /*public*/ function get()
  {
    static $loaded = false;
    
    if(!$loaded)
    {
      $this->addCatOptions();
    }
    
    return parent::get();
  }
  
  /*private*/ function _outGroup($re_id, $level = 0)
  {
		global $REX;
  	if ($level > 100)
    {
      // nur mal so zu sicherheit .. man weiss nie ;)
      echo "select->_outGroup overflow ($groupname)";
      exit;
    }

    $ausgabe = '';
    $group = $this->_getGroup($re_id);
    foreach ($group as $option)
    {
      $name = $option[0];
      $value = $option[1];
      $id = $option[2];
      if($id==0 || !$this->check_perms || ($this->check_perms && $REX['USER']->hasCategoryPerm($option[2],TRUE)))
      {
          $ausgabe .= $this->_outOption($name, $value, $level);
      }elseif(($this->check_perms && $REX['USER']->hasCategoryPerm($option[2],FALSE)))
      {
      	$level--;
      }
      
      $subgroup = $this->_getGroup($id, true);
      if ($subgroup !== false)
      {
        $ausgabe .= $this->_outGroup($id, $level +1);
      }
    }
    return $ausgabe;
  }
  
}

################ Class MediaKategorie Select
class rex_mediacategory_select extends rex_select
{
  var $check_perms;
  var $rootId;
  
  /*public*/ function rex_mediacategory_select($check_perms = true)
  {
    $this->check_perms = $check_perms;
    $this->rootId = null;
    
    parent::rex_select();
  }
  
  /**
   * Kategorie-Id oder ein Array von Kategorie-Ids als Wurzelelemente der Select-Box.
   * 
   * @param $rootId mixed Kategorie-Id oder Array von Kategorie-Ids zur Identifikation der Wurzelelemente. 
   */
  /*public*/ function setRootId($rootId)
  {
    $this->rootId = $rootId;
  }
  
  /*protected*/ function addCatOptions()
  {
    if($this->rootId !== null)
    {
      if(is_array($this->rootId))
      {
        foreach($this->rootId as $rootId)
        {
          if($rootCat = OOMediaCategory::getCategoryById($rootId))
          {
            $this->addCatOption($rootCat);
          }
        }
      }
      else
      {
        if($rootCat = OOMediaCategory::getCategoryById($this->rootId))
        {
          $this->addCatOption($rootCat);
        }
      }
    }
    else
    {
      if ($rootCats = OOMediaCategory::getRootCategories())
      {
        foreach($rootCats as $rootCat)
        {
          $this->addCatOption($rootCat);
        }
      }
    }
  }
  
  /*protected*/ function addCatOption(/*OOMediaCategory*/ $mediacat)
  {
    global $REX;
    
    if(!$this->check_perms ||
        $this->check_perms && $REX['USER']->hasMediaCategoryPerm($mediacat->getId()))
    {
      $mid = $mediacat->getId();
      $mname = $mediacat->getName();
      
      if($REX['USER']->hasPerm('advancedMode[]'))
        $mname .= ' ['. $mid .']';
        
      $this->addOption($mname, $mid, $mid, $mediacat->getParentId());
      $childs = $mediacat->getChildren();
      if (is_array($childs))
      {
        foreach ($childs as $child)
        {
          $this->addCatOption($child);
        }
      }
    }
  }
  
  /*public*/ function get()
  {
    static $loaded = false;
    
    if(!$loaded)
    {
      $this->addCatOptions();
    }
    
    return parent::get();
  }
}