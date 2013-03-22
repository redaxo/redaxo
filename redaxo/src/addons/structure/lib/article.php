<?php

/**
 * Object Oriented Framework: Bildet einen Artikel der Struktur ab
 *
 * @package redaxo\structure
 */
class rex_article extends rex_structure_element
{
    public function __construct($params = false, $clang = false)
    {
        parent :: __construct($params, $clang);
    }

    /**
     * Return an rex_article object based on an id
     *
     * @param int      $article_id
     * @param bool|int $clang
     * @return self
     */
    public static function getArticleById($article_id, $clang = false)
    {
        return parent :: getById($article_id, $clang);
    }

    /**
     * Return the site wide start article
     *
     * @param bool|int $clang
     * @return self
     */
    public static function getSiteStartArticle($clang = false)
    {
        return parent :: getById(rex::getProperty('start_article_id'), $clang);
    }

    /**
     * Return start article for a certain category
     *
     * @param int      $a_category_id
     * @param bool|int $clang
     * @return self
     */
    public static function getCategoryStartArticle($a_category_id, $clang = false)
    {
        return parent :: getById($a_category_id, $clang);
    }

    /**
     * Articles of categories, keyed by category_id
     * @var array
     */
    private static $articleIds = [];

    /**
     * Return a list of articles for a certain category
     *
     * @param int      $a_category_id
     * @param bool     $ignore_offlines
     * @param bool|int $clang
     * @return self[]
     */
    public static function getArticlesOfCategory($a_category_id, $ignore_offlines = false, $clang = false)
    {
        if ($clang === false) {
            $clang = rex_clang::getCurrentId();
        }

        $articlelist = rex_path::addonCache('structure', $a_category_id . '.' . $clang . '.alist');
        if (!file_exists($articlelist)) {
            rex_article_cache::generateLists($a_category_id, $clang);
        }

        $artlist = [];
        if (file_exists($articlelist)) {
            if (!isset(self::$articleIds[$a_category_id])) {
                self::$articleIds[$a_category_id] = rex_file::getCache($articlelist);
            }

            if (self::$articleIds[$a_category_id]) {
                foreach (self::$articleIds[$a_category_id] as $var) {
                    $article = self :: getArticleById($var, $clang);
                    if ($ignore_offlines) {
                        if ($article->isOnline()) {
                            $artlist[] = $article;
                        }
                    } else {
                        $artlist[] = $article;
                    }
                }
            }
        }

        return $artlist;
    }

    /**
     * Return a list of top-level articles
     *
     * @param bool     $ignore_offlines
     * @param bool|int $clang
     * @return self[]
     */
    public static function getRootArticles($ignore_offlines = false, $clang = false)
    {
        return self :: getArticlesOfCategory(0, $ignore_offlines, $clang);
    }

    /**
     * Returns the category id
     *
     * @return int
     */
    public function getCategoryId()
    {
        return $this->isStartArticle() ? $this->getId() : $this->getParentId();
    }

    /**
     * Returns the parent category
     *
     * @return rex_category
     */
    public function getCategory()
    {
        return rex_category :: getCategoryById($this->getCategoryId(), $this->getClang());
    }

    /**
     * Returns the parent object of the article
     *
     * @param bool|int $clang
     * @return self
     */
    public function getParent($clang = false)
    {
        if ($clang === false) {
            $clang = rex_clang::getCurrentId();
        }

        return self::getArticleById($this->_parent_id, $clang);
    }

    /**
     * Returns the path of the category/article
     *
     * @return string
     */
    public function getPath()
    {
        if ($this->isStartArticle()) {
            return $this->_path . $this->_id . '|';
        }

        return $this->_path;
    }

    /**
     * {@inheritDoc}
     */
    public function getValue($value)
    {
        // alias für parent_id -> category_id
        if (in_array($value, ['parent_id', '_parent_id', 'category_id', '_category_id'])) {
            // für die CatId hier den Getter verwenden,
            // da dort je nach ArtikelTyp unterscheidungen getroffen werden müssen
            return $this->getCategoryId();
        }
        return parent::getValue($value);
    }

    /**
     * @param string $value
     * @return bool
     */
    public static function hasValue($value)
    {
        return parent::_hasValue($value, ['art_']);
    }
}
