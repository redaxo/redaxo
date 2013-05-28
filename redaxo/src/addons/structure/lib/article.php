<?php

/**
 * Object Oriented Framework: Bildet einen Artikel der Struktur ab
 *
 * @package redaxo\structure
 */
class rex_article extends rex_structure_element
{
    /**
     * Return the site wide start article
     *
     * @param int $clang
     * @return self
     */
    public static function getSiteStartArticle($clang = null)
    {
        return self::get(rex::getProperty('start_article_id'), $clang);
    }

    /**
     * Return a list of top-level articles
     *
     * @param bool $ignoreOfflines
     * @param int  $clang
     * @return self[]
     */
    public static function getRootArticles($ignoreOfflines = false, $clang = null)
    {
        return self::getChildElements(0, 'alist', $ignoreOfflines, $clang);
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
        return rex_category::get($this->getCategoryId(), $this->getClang());
    }

    /**
     * Returns the parent object of the article
     *
     * @return self
     */
    public function getParent()
    {
        return self::get($this->parent_id, $this->clang_id);
    }

    /**
     * Returns the path of the category/article
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
     * {@inheritDoc}
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
     * @return bool
     */
    public static function hasValue($value)
    {
        return parent::_hasValue($value, ['art_']);
    }
}
