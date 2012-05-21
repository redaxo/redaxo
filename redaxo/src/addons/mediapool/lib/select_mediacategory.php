<?php

################ Class MediaKategorie Select
class rex_mediacategory_select extends rex_select
{
  private
    $check_perms,
    $rootId,
    $loaded = false;

  public function __construct($check_perms = true)
  {
    $this->check_perms = $check_perms;
    $this->rootId = null;

    parent::__construct();
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
    if ($this->rootId !== null)
    {
      if (is_array($this->rootId))
      {
        foreach ($this->rootId as $rootId)
        {
          if ($rootCat = rex_ooMediaCategory::getCategoryById($rootId))
          {
            $this->addCatOption($rootCat);
          }
        }
      }
      else
      {
        if ($rootCat = rex_ooMediaCategory::getCategoryById($this->rootId))
        {
          $this->addCatOption($rootCat);
        }
      }
    }
    else
    {
      if ($rootCats = rex_ooMediaCategory::getRootCategories())
      {
        foreach ($rootCats as $rootCat)
        {
          $this->addCatOption($rootCat);
        }
      }
    }
  }

  protected function addCatOption(rex_ooMediaCategory $mediacat)
  {
    if (!$this->check_perms ||
        $this->check_perms && rex::getUser()->getComplexPerm('media')->hasCategoryPerm($mediacat->getId()))
    {
      $mid = $mediacat->getId();
      $mname = $mediacat->getName();

      if (rex::getUser()->hasPerm('advancedMode[]'))
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
    if (!$this->loaded)
    {
      $this->addCatOptions();
      $this->loaded = true;
    }

    return parent::get();
  }
}
