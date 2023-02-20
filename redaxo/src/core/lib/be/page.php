<?php

/**
 * Backend Page Class.
 *
 * @package redaxo\core\backend
 */
class rex_be_page
{
    /** @var string */
    private $key;
    /** @var string */
    private $fullKey;
    /** @var string */
    private $title;

    /** @var bool|null */
    private $popup;
    /** @var string|null */
    private $href;
    /** @var array<string, string> */
    private $itemAttr = [];
    /** @var array<string, string> */
    private $linkAttr = [];
    /** @var string|null */
    private $path;
    /** @var string|null */
    private $subPath;

    /** @var self|null */
    private $parent;

    /** @var self[] */
    private $subpages = [];

    /** @var bool|null */
    private $isActive;
    /** @var bool */
    private $hidden = false;
    /** @var bool */
    private $hasLayout = true;
    /** @var bool */
    private $hasNavigation = true;
    /** @var bool|null */
    private $pjax;
    /** @var string|null */
    private $icon;
    /** @var string[] */
    private $requiredPermissions = [];

    /**
     * @param string $key
     * @param string $title
     *
     * @throws InvalidArgumentException
     */
    public function __construct($key, $title)
    {
        if (!is_string($key)) {
            throw new InvalidArgumentException('Expecting $key to be a string, ' . gettype($key) . ' given!');
        }
        if (!is_string($title)) {
            throw new InvalidArgumentException('Expecting $title to be a string, ' . gettype($title) . ' given!');
        }

        $this->key = $key;
        $this->fullKey = $key;
        $this->title = $title;
    }

    /**
     * Returns the page key.
     *
     * @return string
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * Returns the full page path.
     *
     * @return string
     */
    public function getFullKey()
    {
        return $this->fullKey;
    }

    /**
     * Sets the page title.
     *
     * @return $this
     */
    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Returns the title.
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Sets whether the page is a popup page.
     *
     * The method adds (or removes) also the rex-popup CSS class and sets hasNavigation to false (true).
     * If $popup is a string, the variable will be used for the onclick attribute.
     *
     * @param bool|string $popup
     *
     * @return $this
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

        return $this;
    }

    /**
     * Returns whether the page is a popup.
     *
     * @return bool
     */
    public function isPopup()
    {
        if (null !== $this->popup) {
            return $this->popup;
        }

        return $this->parent && $this->parent->isPopup();
    }

    /**
     * Sets the page href.
     *
     * @param string|array $href Href string or array of params
     *
     * @return $this
     */
    public function setHref($href)
    {
        if (is_array($href)) {
            $href = rex_url::backendController($href, false);
        }
        $this->href = $href;

        return $this;
    }

    /**
     * Returns whether the page has a custom href.
     *
     * @return bool
     */
    public function hasHref()
    {
        return (bool) $this->href;
    }

    /**
     * Returns the page href.
     *
     * @return string
     */
    public function getHref()
    {
        if ($this->href) {
            return $this->href;
        }
        return rex_url::backendPage($this->getFirstSubpagesLeaf()->getFullKey(), [], false);
    }

    /**
     * Sets an item attribute.
     *
     * @param string $name
     * @param string $value
     *
     * @throws InvalidArgumentException
     *
     * @return $this
     */
    public function setItemAttr($name, $value)
    {
        if (!is_string($name)) {
            throw new InvalidArgumentException('Expecting $name to be a string, ' . gettype($name) . 'given!');
        }
        if (!is_scalar($value)) {
            throw new InvalidArgumentException('Expecting $value to be a scalar, ' . gettype($value) . 'given!');
        }
        $this->itemAttr[$name] = $value;

        return $this;
    }

    /**
     * Returns an item attribute or all item attributes.
     *
     * @template T as ?string
     * @param T $name
     * @param string $default
     * @return string|array Attribute value for given `$name` or attribute array if `$name` is `null`
     * @psalm-return (T is string ? string : array<string, string>)
     */
    public function getItemAttr($name, $default = '')
    {
        // return all attributes if null is passed as name
        if (null === $name) {
            return $this->itemAttr;
        }

        return $this->itemAttr[$name] ?? $default;
    }

    /**
     * Removes an item attribute.
     *
     * @param string $name
     * @return void
     */
    public function removeItemAttr($name)
    {
        unset($this->itemAttr[$name]);
    }

    /**
     * Adds an item class.
     *
     * @param string $class
     *
     * @throws InvalidArgumentException
     *
     * @return $this
     */
    public function addItemClass($class)
    {
        if (!is_string($class)) {
            throw new InvalidArgumentException('Expecting $class to be a string, ' . gettype($class) . 'given!');
        }

        $classAttr = $this->getItemAttr('class');
        if (!preg_match('/\b' . preg_quote($class, '/') . '\b/', $classAttr)) {
            $this->setItemAttr('class', ltrim($classAttr . ' ' . $class));
        }

        return $this;
    }

    /**
     * Removes an item class.
     *
     * @param string $class
     * @return void
     */
    public function removeItemClass($class)
    {
        $this->setItemAttr('class', preg_replace('/\b' . preg_quote($class, '/') . '\b/', '', $this->getItemAttr('class')));
    }

    /**
     * Sets an link attribute.
     *
     * @param string $name
     * @param string $value
     *
     * @throws InvalidArgumentException
     *
     * @return $this
     */
    public function setLinkAttr($name, $value)
    {
        if (!is_string($name)) {
            throw new InvalidArgumentException('Expecting $name to be a string, ' . gettype($name) . 'given!');
        }
        if (!is_scalar($value)) {
            throw new InvalidArgumentException('Expecting $value to be a scalar, ' . gettype($value) . 'given!');
        }
        $this->linkAttr[$name] = $value;

        return $this;
    }

    /**
     * Removes an link attribute.
     *
     * @param string $name
     * @return void
     */
    public function removeLinkAttr($name)
    {
        unset($this->linkAttr[$name]);
    }

    /**
     * Returns an link attribute or all link attributes.
     *
     * @template T as ?string
     * @param T $name
     * @param string $default
     * @return string|array Attribute value for given `$name` or attribute array if `$name` is `null`
     * @psalm-return (T is string ? string : array<string, string>)
     */
    public function getLinkAttr($name, $default = '')
    {
        // return all attributes if null is passed as name
        if (null === $name) {
            return $this->linkAttr;
        }

        return $this->linkAttr[$name] ?? $default;
    }

    /**
     * Adds an link class.
     *
     * @param string $class
     *
     * @throws InvalidArgumentException
     *
     * @return $this
     */
    public function addLinkClass($class)
    {
        if (!is_string($class)) {
            throw new InvalidArgumentException('Expecting $class to be a string, ' . gettype($class) . 'given!');
        }

        $classAttr = $this->getLinkAttr('class');
        if (!preg_match('/\b' . preg_quote($class, '/') . '\b/', $classAttr)) {
            $this->setLinkAttr('class', ltrim($classAttr . ' ' . $class));
        }

        return $this;
    }

    /**
     * Removes an link class.
     *
     * @param string $class
     * @return void
     */
    public function removeLinkClass($class)
    {
        $this->setLinkAttr('class', preg_replace('/\b' . preg_quote($class, '/') . '\b/', '', $this->getLinkAttr('class')));
    }

    /**
     * Set the page path which will be included directly by the core.
     *
     * @param string $path
     *
     * @return $this
     */
    public function setPath($path)
    {
        $this->path = $path;

        return $this;
    }

    /**
     * Returns whether a path is set.
     *
     * @return bool
     */
    public function hasPath()
    {
        return !empty($this->path) || $this->parent && $this->parent->hasPath();
    }

    /**
     * Returns the path which will be included directly by the core.
     *
     * @return string|null
     */
    public function getPath()
    {
        if (!empty($this->path)) {
            return $this->path;
        }
        return $this->parent ? $this->parent->getPath() : null;
    }

    /**
     * Set the page subpath which should be used by the packages to include this page inside their main page.
     *
     * @param string $subPath
     *
     * @return $this
     */
    public function setSubPath($subPath)
    {
        $this->subPath = $subPath;

        return $this;
    }

    /**
     * Returns whether a subpath is set.
     *
     * @return bool
     */
    public function hasSubPath()
    {
        return !empty($this->subPath);
    }

    /**
     * Returns the subpath which should be used by packages to include this page inside their main page.
     *
     * @return string|null
     */
    public function getSubPath()
    {
        return $this->subPath;
    }

    /**
     * Adds a subpage.
     *
     * @return $this
     */
    public function addSubpage(self $subpage)
    {
        $this->subpages[$subpage->getKey()] = $subpage;
        $subpage->parent = $this;
        $subpage->setParentKey($this->getFullKey());

        return $this;
    }

    /**
     * @param string $key
     * @return void
     */
    private function setParentKey($key)
    {
        $this->fullKey = $key . '/' . $this->key;
        foreach ($this->subpages as $subpage) {
            $subpage->setParentKey($this->fullKey);
        }
    }

    /**
     * Sets all subpages.
     *
     * @param self[] $subpages
     *
     * @return $this
     */
    public function setSubpages(array $subpages)
    {
        $this->subpages = [];
        array_walk($subpages, $this->addSubpage(...));

        return $this;
    }

    /**
     * Returns the subpage for the given key.
     *
     * @param string $key
     *
     * @return self|null
     */
    public function getSubpage($key)
    {
        return $this->subpages[$key] ?? null;
    }

    /**
     * Returns all subpages.
     *
     * @return self[]
     */
    public function getSubpages()
    {
        return $this->subpages;
    }

    /**
     * Returns the first leaf of the subpages tree.
     *
     * @return self
     */
    public function getFirstSubpagesLeaf()
    {
        $page = $this;
        while ($subpages = $page->getSubpages()) {
            $page = reset($subpages);
        }
        return $page;
    }

    /**
     * Sets whether the page is active.
     *
     * @param bool $isActive
     *
     * @return $this
     */
    public function setIsActive($isActive = true)
    {
        $this->isActive = (bool) $isActive;

        return $this;
    }

    /**
     * Returns whether the page is active.
     *
     * @return bool
     */
    public function isActive()
    {
        if (null !== $this->isActive) {
            return $this->isActive;
        }
        $page = rex_be_controller::requireCurrentPageObject();
        do {
            if ($page === $this) {
                return true;
            }
        } while ($page = $page->getParent());
        return false;
    }

    /**
     * Returns the parent page object.
     *
     * @return self|null
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * Sets whether the page is hidden.
     *
     * @param bool $hidden
     *
     * @return $this
     */
    public function setHidden($hidden = true)
    {
        $this->hidden = $hidden;

        return $this;
    }

    /**
     * Returns whether the page is hidden.
     *
     * @return bool
     */
    public function isHidden()
    {
        return $this->hidden;
    }

    /**
     * Sets whether the page has layout.
     *
     * @param bool $hasLayout
     *
     * @return $this
     */
    public function setHasLayout($hasLayout)
    {
        $this->hasLayout = $hasLayout;

        return $this;
    }

    /**
     * Returns whether tha page has layout.
     *
     * @return bool
     */
    public function hasLayout()
    {
        return $this->hasLayout && (!$this->parent || $this->parent->hasLayout());
    }

    /**
     * Sets whether the page has a navigation.
     *
     * @param bool $hasNavigation
     *
     * @return $this
     */
    public function setHasNavigation($hasNavigation)
    {
        $this->hasNavigation = $hasNavigation;

        return $this;
    }

    /**
     * Returns whether the page has a navigation.
     *
     * @return bool
     */
    public function hasNavigation()
    {
        return $this->hasNavigation && (!$this->parent || $this->parent->hasNavigation());
    }

    /**
     * Sets whether the page allows pjax.
     *
     * @param bool $pjax
     *
     * @return $this
     */
    public function setPjax($pjax = true)
    {
        $this->pjax = $pjax;

        return $this;
    }

    /**
     * Returns whether the page allows pjax.
     *
     * @return bool
     */
    public function allowsPjax()
    {
        if (null !== $this->pjax) {
            return $this->pjax;
        }
        if ($this->parent) {
            return $this->parent->allowsPjax();
        }
        return false;
    }

    /**
     * Sets whether the page has an icon.
     *
     * @param string $icon
     *
     * @return $this
     */
    public function setIcon($icon)
    {
        $this->icon = $icon;

        return $this;
    }

    /**
     * Returns the icon.
     *
     * @return string|null
     */
    public function getIcon()
    {
        return $this->icon;
    }

    /**
     * Returns whether the page has an icon.
     *
     * @return bool
     */
    public function hasIcon()
    {
        return !empty($this->icon);
    }

    /**
     * Sets the required permissions.
     *
     * @param string[]|string $perm
     *
     * @return $this
     */
    public function setRequiredPermissions($perm)
    {
        $this->requiredPermissions = (array) $perm;

        return $this;
    }

    /**
     * Returns the required permission.
     *
     * @return string[]
     */
    public function getRequiredPermissions()
    {
        return $this->requiredPermissions;
    }

    /**
     * Checks whether the given user has permission for the page.
     *
     * @return bool
     */
    public function checkPermission(rex_user $user)
    {
        foreach ($this->requiredPermissions as $perm) {
            if (!$user->hasPerm($perm)) {
                return false;
            }
        }
        if ($parent = $this->getParent()) {
            return $parent->checkPermission($user);
        }
        return true;
    }
}
