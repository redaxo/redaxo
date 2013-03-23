<?php

/**
 * Object Oriented Framework: Bildet eine Kategorie der Struktur ab
 *
 * @package redaxo\structure
 */
class rex_category extends rex_structure_element
{
    /**
     * Return an rex_category object based on an id
     *
     * @param int $categoryId
     * @param int $clang
     * @return self
     */
    public static function get($categoryId, $clang = null)
    {
        return parent::get($categoryId, $clang);
    }

    /**
     * Return a list of top level categories, ie.
     * categories that have no parent.
     * Returns an array of rex_category objects sorted by $priority.
     *
     * If $ignore_offlines is set to TRUE,
     * all categories with status 0 will be
     * excempt from this list!
     *
     * @param bool $ignoreOfflines
     * @param int  $clang
     * @return self[]
     */
    public static function getRootCategories($ignoreOfflines = false, $clang = null)
    {
        return self::getChildElements(0, 'clist', $ignoreOfflines, $clang);
    }

    /**
     * {@inheritDoc}
     */
    public function getPriority()
    {
        return $this->catpriority;
    }

    /**
     * Return a list of all subcategories.
     * Returns an array of rex_category objects sorted by $priority.
     *
     * If $ignore_offlines is set to TRUE,
     * all categories with status 0 will be
     * excempt from this list!
     *
     * @param bool $ignoreOfflines
     * @param int  $clang
     * @return self[]
     */
    public function getChildren($ignoreOfflines = false, $clang = null)
    {
        return self::getChildElements($this->id, 'clist', $ignoreOfflines, $clang ?: $this->clang);
    }

    /**
     * Returns the parent category
     *
     * @param int $clang
     * @return self
     */
    public function getParent($clang = null)
    {
        return self::get($this->parent_id, $clang ?: $this->clang);
    }

    /**
     * Returns TRUE if this category is the direct
     * parent of the other category.
     *
     * @param self $otherCat
     * @return boolean
     */
    public function isParent(self $otherCat)
    {
         return $this->getId() == $otherCat->getParentId() &&
             $this->getClang() == $otherCat->getClang();
    }

    /**
     * Returns TRUE if this category is an ancestor
     * (parent, grandparent, greatgrandparent, etc)
     * of the other category.
     *
     * @param self $otherCat
     * @return boolean
     */
    public function isAncestor(self $otherCat)
    {
        return in_array($this->id, explode('|', $otherCat->getPath()));
    }

    /**
     * Return a list of articles in this category
     * Returns an array of rex_article objects sorted by $priority.
     *
     * If $ignore_offlines is set to TRUE,
     * all articles with status 0 will be
     * excempt from this list!
     *
     * @param bool $ignoreOfflines
     * @return rex_article[]
     */
    public function getArticles($ignoreOfflines = false)
    {
        return rex_article::getChildElements($this->id, 'alist', $ignoreOfflines, $this->clang);
    }

    /**
     * Return the start article for this category
     *
     * @return rex_article
     */
    public function getStartArticle()
    {
        return rex_article::get($this->id, $this->clang);
    }

    /**
     * Returns the name of the category
     *
     * @return string
     */
    public function getName()
    {
        return $this->catname;
    }

    /**
     * Returns the path of the category
     *
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * @param string $value
     * @return boolean
     */
    public static function hasValue($value)
    {
        return parent::_hasValue($value, ['cat_']);
    }
}
