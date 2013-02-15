<?php

class rex_be_page
{
  private
    $key,
    $fullKey,
    $title,

    $popup = false,
    $href,
    $itemAttr = array(),
    $linkAttr = array(),
    $path,
    $subPath,

    $parent,
    $subpages = array(),

    $isActive = null,
    $hidden = false,
    $hasLayout = true,
    $hasNavigation = true,
    $requiredPermissions = array();

  public function __construct($key, $title)
  {
    if (!is_string($key)) {
      throw new rex_exception('Expecting $key to be a string, ' . gettype($key) . ' given!');
    }
    if (!is_string($title)) {
      throw new rex_exception('Expecting $title to be a string, ' . gettype($title) . ' given!');
    }

    $this->key = $key;
    $this->fullKey = $key;
    $this->title = $title;
  }

  public function getKey()
  {
    return $this->key;
  }

  public function getFullKey()
  {
    return $this->fullKey;
  }

  public function getTitle()
  {
    return $this->title;
  }

  /**
   * Sets whether the page is a popup page
   *
   * The method adds (or removes) also the rex-popup CSS class and sets hasNavigation to false (true).
   * If $popup is a string, the variable will be used for the onclick attribute.
   *
   * @param bool|string $popup
   */
  public function setPopup($popup)
  {
    if ($popup) {
      $this->popup = true;
      $this->setHasNavigation(false);
      $this->addItemClass('rex-popup');
      $this->addLinkClass('rex-popup');
      if (is_string($popup)) {
        $this->setLinkAttr('onclick', $popup);
      }
    } else {
      $this->popup = false;
      $this->setHasNavigation(true);
      $this->removeItemClass('rex-popup');
      $this->removeLinkClass('rex-popup');
      $this->removeLinkAttr('onclick');
    }
  }

  /**
   * @return bool
   */
  public function isPopup()
  {
    return $this->popup;
  }

  public function setHref($href)
  {
    if (is_array($href)) {
      $href = rex_url::backendController($href);
    }
    $this->href = $href;
  }

  public function getHref()
  {
    if ($this->href) {
      return htmlspecialchars_decode($this->href);
    }
    return htmlspecialchars_decode(rex_url::backendPage($this->getFullKey()));
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

  public function getItemAttr($name, $default = '')
  {
    // return all attributes if null is passed as name
    if ($name === null) {
      return $this->itemAttr;
    }

    return isset($this->itemAttr[$name]) ? $this->itemAttr[$name] : $default;
  }

  public function removeItemAttr($name)
  {
    unset($this->itemAttr[$name]);
  }

  public function addItemClass($class)
  {
    if (!is_string($class)) {
      throw new rex_exception('Expecting $class to be a string, ' . gettype($class) . 'given!');
    }
    $classAttr = $this->getItemAttr('class');
    if (!preg_match('/\b' . preg_quote($class, '/') . '\b/', $classAttr)) {
      $this->setItemAttr('class', ltrim($classAttr . ' ' . $class));
    }
  }

  public function removeItemClass($class)
  {
    $this->setItemAttr('class', preg_replace('/\b' . preg_quote($class, '/') . '\b/', '', $this->getItemAttr('class')));
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

  public function removeLinkAttr($name)
  {
    unset($this->linkAttr[$name]);
  }

  public function getLinkAttr($name, $default = '')
  {
    // return all attributes if null is passed as name
    if ($name === null) {
      return $this->linkAttr;
    }

    return isset($this->linkAttr[$name]) ? $this->linkAttr[$name] : $default;
  }

  public function addLinkClass($class)
  {
    $this->setLinkAttr('class', ltrim($this->getLinkAttr('class') . ' ' . $class));
  }

  public function removeLinkClass($class)
  {
    $this->setLinkAttr('class', preg_replace('/\b' . preg_quote($class, '/') . '\b/', '', $this->getLinkAttr('class')));
  }

  /**
   * Set the page path which will be included directly by the core
   *
   * @param string $path
   */
  public function setPath($path)
  {
    $this->path = $path;
  }

  /**
   * Returns whether a path is set
   *
   * @return bool
   */
  public function hasPath()
  {
    return !empty($this->path) || $this->parent && $this->parent->hasPath();
  }

  /**
   * Returns the path which will be included directly by the core
   *
   * @return string
   */
  public function getPath()
  {
    if (!empty($this->path)) {
      return $this->path;
    }
    return $this->parent ? $this->parent->getPath() : null;
  }

  /**
   * Set the page subpath which should be used by the packages to include this page inside their main page
   *
   * @param string $subPath
   */
  public function setSubPath($subPath)
  {
    $this->subPath = $subPath;
  }

  /**
   * Returns whether a subpath is set
   *
   * @return bool
   */
  public function hasSubPath()
  {
    return !empty($this->subPath);
  }

  /**
   * Returns the subpath which should by used by packages to include this page inside their main page
   *
   * @return string
   */
  public function getSubPath()
  {
    return $this->subPath;
  }

  /**
   * Adds a subpage
   *
   * @param self $subpage
   */
  public function addSubpage(self $subpage)
  {
    $this->subpages[$subpage->getKey()] = $subpage;
    $subpage->parent = $this;
    $subpage->setParentKey($this->getFullKey());
  }

  private function setParentKey($key)
  {
    $this->fullKey = $key . '/' . $this->key;
    foreach ($this->subpages as $subpage) {
      $subpage->setParentKey($this->fullKey);
    }
  }

  /**
   * Sets all subpages
   *
   * @param self[] $subpages
   */
  public function setSubpages(array $subpages)
  {
    $this->subpages = array();
    array_walk($subpages, array($this, 'addSubpage'));
  }

  /**
   * Returns the subpage for the given key
   *
   * @param string $key
   * @return self
   */
  public function getSubpage($key)
  {
    return isset($this->subpages[$key]) ? $this->subpages[$key] : null;
  }

  /**
   * Returns all subpages
   *
   * @return self[]
   */
  public function getSubpages()
  {
    return $this->subpages;
  }

  public function setIsActive($isActive = true)
  {
    $this->isActive = $isActive;
  }

  public function isActive()
  {
    if ($this->isActive !== null) {
      return $this->isActive;
    }
    $page = rex_be_controller::getCurrentPageObject();
    do {
      if ($page === $this) {
        return true;
      }
    } while ($page = $page->getParent());
    return null;
  }

  /**
   * @return self
   */
  public function getParent()
  {
    return $this->parent;
  }

  public function setHidden($hidden = true)
  {
    $this->hidden = $hidden;
  }

  public function isHidden()
  {
    return $this->hidden;
  }

  public function setHasLayout($hasLayout)
  {
    $this->hasLayout = $hasLayout;
  }

  public function hasLayout()
  {
    return $this->hasLayout && (!$this->parent || $this->parent->hasLayout());
  }

  public function setHasNavigation($hasNavigation)
  {
    $this->hasNavigation = $hasNavigation;
  }

  public function hasNavigation()
  {
    return $this->hasNavigation && (!$this->parent || $this->parent->hasNavigation());
  }

  /**
   * @param array|string $perm
   */
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
    if ($parent = $this->getParent()) {
      return $parent->checkPermission($rexUser);
    }
    return true;
  }
}
