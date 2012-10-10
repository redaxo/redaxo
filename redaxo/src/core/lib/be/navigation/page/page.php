<?php

class rex_be_page implements rex_be_page_container
{
  private
    $title,

    $href,
    $linkAttr,
    $itemAttr,

    $subPages,

    $isCorePage,
    $hasLayout = true,
    $hasNavigation,
    $activateCondition,
    $requiredPermissions,
    $path;

  public function __construct($title, array $activateCondition = array(), $hidden = false)
  {
    if (!is_string($title)) {
      throw new rex_exception('Expecting $title to be a string, ' . gettype($title) . ' given!');
    }

    if (!is_bool($hidden)) {
      throw new rex_exception('Expecting $hidden to be a boolean, ' . gettype($hidden) . 'given!');
    }

    $this->title = $title;
    $this->subPages = array();
    $this->itemAttr = array();
    $this->linkAttr = array();

    $this->isCorePage = false;
    $this->hasNavigation = true;
    $this->activateCondition = $activateCondition;
    $this->requiredPermissions = array();
    $this->hidden = $hidden;
  }

  public function getPage()
  {
    return $this;
  }

  public function getItemAttr($name, $default = '')
  {
    // return all attributes if null is passed as name
    if ($name === null) {
      return $this->itemAttr;
    }

    return isset($this->itemAttr[$name]) ? $this->itemAttr[$name] : $default;
  }

  public function setItemAttr($name, $value)
  {
    if (!is_string($name)) {
      throw new rex_exception('Expecting $name to be a string, ' . gettype($name) . 'given!');
    }
    if (!is_scalar($value)) {
      throw new rex_exception('Expecting $value to be a scalar, ' . gettype($value) . 'given!');
    }
    $this->itemAttr[$name] = $value;
  }

  public function addItemClass($class)
  {
    if (!is_string($class)) {
      throw new rex_exception('Expecting $class to be a string, ' . gettype($class) . 'given!');
    }
    $this->setItemAttr('class', ltrim($this->getItemAttr('class') . ' ' . $class));
  }

  public function getLinkAttr($name, $default = '')
  {
    // return all attributes if null is passed as name
    if ($name === null) {
      return $this->linkAttr;
    }

    return isset($this->linkAttr[$name]) ? $this->linkAttr[$name] : $default;
  }

  public function setLinkAttr($name, $value)
  {
    if (!is_string($name)) {
      throw new rex_exception('Expecting $name to be a string, ' . gettype($name) . 'given!');
    }
    if (!is_scalar($value)) {
      throw new rex_exception('Expecting $value to be a scalar, ' . gettype($value) . 'given!');
    }
    $this->linkAttr[$name] = $value;
  }

  public function addLinkClass($class)
  {
    $this->setLinkAttr('class', ltrim($this->getLinkAttr('class') . ' ' . $class));
  }

  public function setHref($href)
  {
    $this->href = $href;
  }

  public function getHref()
  {
    return $this->href;
  }

  public function setHidden($hidden = true)
  {
    $this->hidden = $hidden;
  }

  public function getHidden()
  {
    return $this->hidden;
  }

  public function setIsCorePage($isCorePage)
  {
    $this->isCorePage = $isCorePage;
  }

  public function setHasLayout($hasLayout)
  {
    $this->hasLayout = $hasLayout;
  }

  public function setHasNavigation($hasNavigation)
  {
    $this->hasNavigation = $hasNavigation;
  }

  public function addSubPage(self $subpage)
  {
    $this->subPages[] = $subpage;
  }

  public function getSubPages()
  {
    return $this->subPages;
  }

  /**
   * @return rex_be_page
   */
  public function getActiveSubPage()
  {
    foreach ($this->getSubPages() as $subpage) {
      if ($subpage->isActive()) {
        return $subpage;
      }
    }
    return null;
  }

  public function getTitle()
  {
    return $this->title;
  }

  public function setActivateCondition(array $activateCondition)
  {
    $this->activateCondition = $activateCondition;
  }

  public function getActivateCondition()
  {
    return $this->activateCondition;
  }

  public function isActive()
  {
    $condition = $this->getActivateCondition();
    if (empty($condition)) {
      return false;
    }
    foreach ($condition as $k => $v) {
      $v = (array) $v;
      if (!in_array(rex_request($k), $v)) {
        return false;
      }
    }
    return true;
  }

  public function isCorePage()
  {
    return $this->isCorePage;
  }

  public function hasLayout()
  {
    return $this->hasLayout;
  }

  public function hasNavigation()
  {
    return $this->hasNavigation;
  }

  public function setRequiredPermissions($perm)
  {
    $this->requiredPermissions = (array) $perm;
  }

  public function getRequiredPermissions()
  {
    return $this->requiredPermissions;
  }

  public function checkPermission(rex_user $rexUser)
  {
    foreach ($this->requiredPermissions as $perm) {
      if (!$rexUser->hasPerm($perm)) {
        return false;
      }
    }
    return true;
  }

  public function setPath($path)
  {
    $this->path = $path;
  }

  public function hasPath()
  {
    return !empty($this->path);
  }

  public function getPath()
  {
    return $this->path;
  }
}
