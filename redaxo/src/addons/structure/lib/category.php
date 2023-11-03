<?php

/**
 * Object Oriented Framework: Bildet eine Kategorie der Struktur ab.
 *
 * @package redaxo\structure
 */
class rex_category extends rex_structure_element
{
    /**
     * Return the current category.
     *
     * @param int $clang
     *
     * @return null|self
     */
    public static function getCurrent($clang = null)
    {
        $article = rex_article::getCurrent($clang);

        return $article ? $article->getCategory() : null;
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
     *
     * @return self[]
     */
    public static function getRootCategories($ignoreOfflines = false, $clang = null)
    {
        return self::getChildElements(0, 'clist', $ignoreOfflines, $clang);
    }

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
     *
     * @return self[]
     */
    public function getChildren($ignoreOfflines = false)
    {
        return self::getChildElements($this->id, 'clist', $ignoreOfflines, $this->clang_id);
    }

    /**
     * Returns the parent category.
     *
     * @return static|null
     */
    public function getParent()
    {
        return self::get($this->parent_id, $this->clang_id);
    }

    /**
     * Returns TRUE if this category is the direct
     * parent of the other category.
     *
     * @return bool
     */
    public function isParent(self $otherCat)
    {
        return $this->getId() == $otherCat->getParentId() &&
             $this->getClangId() == $otherCat->getClangId();
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
     *
     * @return rex_article[]
     */
    public function getArticles($ignoreOfflines = false)
    {
        return rex_article::getChildElements($this->id, 'alist', $ignoreOfflines, $this->clang_id);
    }

    /**
     * Return the start article for this category.
     *
     * @return rex_article
     */
    public function getStartArticle()
    {
        $article = rex_article::get($this->id, $this->clang_id);
        assert($article instanceof rex_article);
        return $article;
    }

    /**
     * Returns the name of the category.
     *
     * @return string
     */
    public function getName()
    {
        return $this->catname;
    }

    /**
     * Returns the path of the category.
     *
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * @param string $value
     *
     * @return bool
     */
    public static function hasValue($value)
    {
        return parent::_hasValue($value, ['cat_']);
    }

    public function isPermitted()
    {
        return (bool) rex_extension::registerPoint(new rex_extension_point('CAT_IS_PERMITTED', true, ['element' => $this]));
    }
}
