<?php

/**
 * Object Oriented Framework: Bildet einen Artikel der Struktur ab.
 *
 * @package redaxo\structure
 */
class rex_article extends rex_structure_element
{
    /**
     * Return the site wide start article id.
     *
     * @return int
     */
    public static function getSiteStartArticleId()
    {
        return rex_config::get('structure', 'start_article_id', 1);
    }

    /**
     * Return the site wide start article.
     *
     * @param int $clang
     *
     * @return self
     */
    public static function getSiteStartArticle($clang = null)
    {
        return self::get(self::getSiteStartArticleId(), $clang);
    }

    /**
     * Return the site wide notfound article id.
     *
     * @return int
     */
    public static function getNotfoundArticleId()
    {
        return rex_config::get('structure', 'notfound_article_id', 1);
    }

    /**
     * Return the site wide notfound article.
     *
     * @param int $clang
     *
     * @return self
     */
    public static function getNotfoundArticle($clang = null)
    {
        return self::get(self::getNotfoundArticleId(), $clang);
    }

    /**
     * Return a list of top-level articles.
     *
     * @param bool $ignoreOfflines
     * @param int  $clang
     *
     * @return self[]
     */
    public static function getRootArticles($ignoreOfflines = false, $clang = null)
    {
        return self::getChildElements(0, 'alist', $ignoreOfflines, $clang);
    }

    /**
     * Returns the category id.
     *
     * @return int
     */
    public function getCategoryId()
    {
        return $this->isStartArticle() ? $this->getId() : $this->getParentId();
    }

    /**
     * Returns the parent category.
     *
     * @return rex_category
     */
    public function getCategory()
    {
        return rex_category::get($this->getCategoryId(), $this->getClang());
    }

    /**
     * Returns the parent object of the article.
     *
     * @return self
     */
    public function getParent()
    {
        return self::get($this->parent_id, $this->clang_id);
    }

    /**
     * Returns the path of the category/article.
     *
     * @return string
     */
    public function getPath()
    {
        if ($this->isStartArticle()) {
            return $this->path . $this->id . '|';
        }

        return $this->path;
    }

    /**
     * {@inheritdoc}
     */
    public function getValue($value)
    {
        // alias für parent_id -> category_id
        if (in_array($value, ['parent_id', 'category_id'])) {
            // für die CatId hier den Getter verwenden,
            // da dort je nach ArtikelTyp unterscheidungen getroffen werden müssen
            return $this->getCategoryId();
        }
        return parent::getValue($value);
    }

    /**
     * @param string $value
     *
     * @return bool
     */
    public static function hasValue($value)
    {
        return parent::_hasValue($value, ['art_']);
    }

    /**
     * {@inheritdoc}
     */
    public function isPermitted()
    {
        return (bool) rex_extension::registerPoint(new rex_extension_point('ART_IS_PERMITTED', true, ['element' => $this]));
    }
}
