<?php

/**
 * Object Oriented Framework: Bildet eine Kategorie der Struktur ab
 * @package redaxo5
 */

class rex_category extends rex_structure_element
{
    public function __construct($params = false, $clang = false)
    {
        parent :: __construct($params, $clang);
    }

    /**
     * Return an rex_category object based on an id
     *
     * @param int      $category_id
     * @param bool|int $clang
     * @return self
     */
    public static function getCategoryById($category_id, $clang = false)
    {
        return parent :: getById($category_id, $clang);
    }

    /**
     * children of categories, keyed by category_id (parent ids)
     * @var array
     */
    private static $childIds = array();

    /**
     * Return all Children by id
     *
     * @param int      $cat_parent_id
     * @param bool     $ignore_offlines
     * @param bool|int $clang
     * @return self[]
     */
    public static function getChildrenById($cat_parent_id, $ignore_offlines = false, $clang = false)
    {
        $cat_parent_id = (int) $cat_parent_id;

        if ($cat_parent_id < 0)
            return array();

        if ($clang === false) {
            $clang = rex_clang::getCurrentId();
        }

        $categorylist = rex_path::addonCache('structure', $cat_parent_id . '.' . $clang . '.clist');

        $catlist = array();

        if (!file_exists($categorylist)) {
            rex_article_cache::generateLists($cat_parent_id);
        }

        if (file_exists($categorylist)) {
            if (!isset (self::$childIds[$cat_parent_id])) {
                self::$childIds[$cat_parent_id] = rex_file::getCache($categorylist);
            }

            if (isset (self::$childIds[$cat_parent_id]) && is_array(self::$childIds[$cat_parent_id])) {
                foreach (self::$childIds[$cat_parent_id] as $var) {
                    $category = self :: getCategoryById($var, $clang);
                    if ($ignore_offlines) {
                        if ($category->isOnline()) {
                            $catlist[] = $category;
                        }
                    } else {
                        $catlist[] = $category;
                    }
                }
            }
        }

        return $catlist;
    }

    /**
     * {@inheritDoc}
     */
    public function getPriority()
    {
        return $this->_catprior;
    }

    /**
     * Return a list of top level categories, ie.
     * categories that have no parent.
     * Returns an array of rex_category objects sorted by $prior.
     *
     * If $ignore_offlines is set to TRUE,
     * all categories with status 0 will be
     * excempt from this list!
     *
     * @param bool     $ignore_offlines
     * @param bool|int $clang
     * @return self[]
     */
    public static function getRootCategories($ignore_offlines = false, $clang = false)
    {
        if ($clang === false) {
            $clang = rex_clang::getCurrentId();
        }

        return self :: getChildrenById(0, $ignore_offlines, $clang);
    }

    /**
     * Return a list of all subcategories.
     * Returns an array of rex_category objects sorted by $prior.
     *
     * If $ignore_offlines is set to TRUE,
     * all categories with status 0 will be
     * excempt from this list!
     *
     * @param bool     $ignore_offlines
     * @param bool|int $clang
     * @return self[]
     */
    public function getChildren($ignore_offlines = false, $clang = false)
    {
        if ($clang === false) {
            $clang = rex_clang::getCurrentId();
        }

        return self :: getChildrenById($this->_id, $ignore_offlines, $clang);
    }

    /**
     * Returns the parent category
     *
     * @param bool|int $clang
     * @return self
     */
    public function getParent($clang = false)
    {
        if ($clang === false) {
            $clang = rex_clang::getCurrentId();
        }

        return self :: getCategoryById($this->_re_id, $clang);
    }

    /**
     * Returns TRUE if this category is the direct
     * parent of the other category.
     *
     * @param self $other_cat
     * @return boolean
     */
    public function isParent($other_cat)
    {
         return $this->getId() == $other_cat->getParentId() &&
                        $this->getClang() == $other_cat->getClang();
    }

    /**
     * Returns TRUE if this category is an ancestor
     * (parent, grandparent, greatgrandparent, etc)
     * of the other category.
     *
     * @param self $other_cat
     * @return boolean
     */
    public function isAncestor($other_cat)
    {
        $category = self :: _getCategoryObject($other_cat);
        return in_array($this->_id, explode('|', $category->getPath()));
    }

    /**
     * Return a list of articles in this category
     * Returns an array of rex_article objects sorted by $prior.
     *
     * If $ignore_offlines is set to TRUE,
     * all articles with status 0 will be
     * excempt from this list!
     *
     * @param bool $ignore_offlines
     * @return rex_article[]
     */
    public function getArticles($ignore_offlines = false)
    {
        return rex_article :: getArticlesOfCategory($this->_id, $ignore_offlines, $this->_clang);
    }

    /**
     * Return the start article for this category
     *
     * @return rex_article
     */
    public function getStartArticle()
    {
        return rex_article :: getCategoryStartArticle($this->_id, $this->_clang);
    }

    /**
     * Returns the name of the article
     *
     * @return string
     */
    public function getName()
    {
        return $this->_catname;
    }

    /**
     * Returns the path of the category
     *
     * @return string
     */
    public function getPath()
    {
        return $this->_path;
    }

    /**
     * @param int|self $category
     * @param bool|int $clang
     * @return mixed
     */
    public static function _getCategoryObject($category, $clang = false)
    {
        if (is_object($category)) {
            return $category;
        } elseif (is_int($category)) {
            return self :: getCategoryById($category, $clang);
        } elseif (is_array($category)) {
            $catlist = array();
            foreach ($category as $cat) {
                $catobj = self :: _getCategoryObject($cat, $clang);
                if (is_object($catobj)) {
                    $catlist[] = $catobj;
                } else {
                    return null;
                }
            }
            return $catlist;
        }
        return null;
    }

    /**
     * @param string $value
     * @return boolean
     */
    public static function hasValue($value)
    {
        return parent::_hasValue($value, array('cat_'));
    }
}
