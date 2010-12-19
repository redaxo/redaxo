<?php

################ Class MediaKategorie Select
class rex_mediacategory_select extends rex_select
{
  var $check_perms;
  var $rootId;
  
  public function rex_mediacategory_select($check_perms = true)
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
  public function setRootId($rootId)
  {
    $this->rootId = $rootId;
  }
  
  protected function addCatOptions()
  {
    if($this->rootId !== null)
    {
      if(is_array($this->rootId))
      {
        foreach($this->rootId as $rootId)
        {
          if($rootCat = rex_oomediaCategory::getCategoryById($rootId))
          {
            $this->addCatOption($rootCat);
          }
        }
      }
      else
      {
        if($rootCat = rex_oomediaCategory::getCategoryById($this->rootId))
        {
          $this->addCatOption($rootCat);
        }
      }
    }
    else
    {
      if ($rootCats = rex_oomediaCategory::getRootCategories())
      {
        foreach($rootCats as $rootCat)
        {
          $this->addCatOption($rootCat);
        }
      }
    }
  }
  
  protected function addCatOption(/*rex_oomediaCategory*/ $mediacat)
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
  
  public function get()
  {
    static $loaded = false;
    
    if(!$loaded)
    {
      $this->addCatOptions();
    }
    
    return parent::get();
  }
}