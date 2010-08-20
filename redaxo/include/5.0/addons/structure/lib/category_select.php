<?php

class rex_category_select extends rex_select
{
  private $ignore_offlines;
  private $clang;
  private $check_perms;
  private $rootId;

  public function rex_category_select($ignore_offlines = false, $clang = false, $check_perms = true, $add_homepage = true)
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
  public function setRootId($rootId)
  {
    $this->rootId = $rootId;
  }
  
  protected function addCatOptions()
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
  
  protected function addCatOption(/*OOCategory*/ $cat, $group = null)
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

  public function get()
  {
    static $loaded = false;
    
    if(!$loaded)
    {
      $this->addCatOptions();
    }
    
    return parent::get();
  }
  
  private function _outGroup($re_id, $level = 0)
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
