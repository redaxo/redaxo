<?php

class rex_be_page implements rex_be_page_container
{
  private
    $key,
    $fullKey,
    $title,

    $href,
    $itemAttr = array(),
    $linkAttr = array(),
    $path,
    $subPath,

    $parent,
    $subPages = array(),

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

  /* (non-PHPdoc)
   * @see rex_be_page_container::getPage()
   */
  public function getPage()
  {
    return $this;
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

  public function addSubPage(self $subpage)
  {
    $this->subPages[$subpage->getKey()] = $subpage;
    $subpage->parent = $this;
    $subpage->setParentKey($this->getFullKey());
  }

  private function setParentKey($key)
  {
    $this->fullKey = $key . '/' . $this->key;
    foreach ($this->subPages as $subPage) {
      $subPage->setParentKey($this->fullKey);
    }
  }

  public function setSubPages(array $subpages)
  {
    $this->subPages = array();
    array_walk($subpages, array($this, 'addSubPage'));
  }

  /**
   * @param string $key
   * @return self
   */
  public function getSubPage($key)
  {
    return isset($this->subPages[$key]) ? $this->subPages[$key] : null;
  }

  /**
   * @return array[self]
   */
  public function getSubPages()
  {
    return $this->subPages;
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
      $page = $page->getPage();
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

  public function _set($key, $value)
  {
    if (!is_string($key) || strtolower($key) == 'subpages')
      return;

    $setter = array($this, $key == 'perm' ? 'setRequiredPermissions' : 'set' . ucfirst($key));
    if (is_callable($setter)) {
      return call_user_func($setter, $value);
    }

    $setter = array($this, 'add' . ucfirst($key));
    if (is_callable($setter)) {
      if (is_array($value)) {
        foreach ($value as $v) {
          call_user_func($setter, $v);
        }
      } else {
        call_user_func($setter, $value);
      }
    }
  }
}
