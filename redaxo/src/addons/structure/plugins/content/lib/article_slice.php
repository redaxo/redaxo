<?php

/**
 * The rex_article_slice class is an object wrapper over the database table rex_article_slice.
 * Together with rex_article and rex_category it provides an object oriented
 * Framework for accessing vital parts of your website.
 * This framework can be used in Modules, Templates and PHP-Slices!
 *
 * @package redaxo\structure\content
 */
class rex_article_slice
{
    private $_id;
    private $_article_id;
    private $_clang;
    private $_ctype;
    private $_priority;
    private $_module_id;

    private $_createdate;
    private $_updatedate;
    private $_createuser;
    private $_updateuser;
    private $_revision;

    private $_values;
    private $_media;
    private $_medialists;
    private $_links;
    private $_linklists;

    /**
     * Constructor.
     *
     * @param int    $id
     * @param int    $article_id
     * @param int    $clang
     * @param int    $ctype
     * @param int    $module_id
     * @param int    $priority
     * @param int    $createdate
     * @param int    $updatedate
     * @param string $createuser
     * @param string $updateuser
     * @param int    $revision
     * @param array  $values
     * @param array  $media
     * @param array  $medialists
     * @param array  $links
     * @param array  $linklists
     */
    protected function __construct(
        $id, $article_id, $clang, $ctype, $module_id, $priority,
        $createdate, $updatedate, $createuser, $updateuser, $revision,
        $values, $media, $medialists, $links, $linklists)
    {
        $this->_id = $id;
        $this->_article_id = $article_id;
        $this->_clang = $clang;
        $this->_ctype = $ctype;
        $this->_priority = $priority;
        $this->_module_id = $module_id;

        $this->_createdate = $createdate;
        $this->_updatedate = $updatedate;
        $this->_createuser = $createuser;
        $this->_updateuser = $updateuser;
        $this->_revision = $revision;

        $this->_values = $values;
        $this->_media = $media;
        $this->_medialists = $medialists;
        $this->_links = $links;
        $this->_linklists = $linklists;
    }

    /**
     * Return an ArticleSlice by its id.
     *
     * @param int      $an_id
     * @param bool|int $clang
     * @param int      $revision
     *
     * @return self|null
     */
    public static function getArticleSliceById($an_id, $clang = false, $revision = 0)
    {
        if (false === $clang) {
            $clang = rex_clang::getCurrentId();
        }

        return self::getSliceWhere(
            'id=? AND clang_id=? and revision=?',
            [$an_id, $clang, $revision]
        );
    }

    /**
     * Return the first slice for an article.
     * This can then be used to iterate over all the
     * slices in the order as they appear using the
     * getNextSlice() function.
     *
     * @param int      $an_article_id
     * @param bool|int $clang
     * @param int      $revision
     *
     * @return self|null
     */
    public static function getFirstSliceForArticle($an_article_id, $clang = false, $revision = 0)
    {
        if (false === $clang) {
            $clang = rex_clang::getCurrentId();
        }

        foreach (range(1, 20) as $ctype) {
            $slice = self::getFirstSliceForCtype($ctype, $an_article_id, $clang, $revision);
            if (null !== $slice) {
                return $slice;
            }
        }

        return null;
    }

    /**
     * Returns the first slice of the given ctype of an article.
     *
     * @param int      $ctype
     * @param int      $an_article_id
     * @param bool|int $clang
     * @param int      $revision
     *
     * @return self|null
     */
    public static function getFirstSliceForCtype($ctype, $an_article_id, $clang = false, $revision = 0)
    {
        if (false === $clang) {
            $clang = rex_clang::getCurrentId();
        }

        return self::getSliceWhere(
            'article_id=? AND clang_id=? AND ctype_id=? AND priority=1 AND revision=?',
            [$an_article_id, $clang, $ctype, $revision]
        );
    }

    /**
     * Return all slices for an article that have a certain
     * clang or revision.
     *
     * @param int      $an_article_id
     * @param bool|int $clang
     * @param int      $revision
     *
     * @return self[]
     */
    public static function getSlicesForArticle($an_article_id, $clang = false, $revision = 0)
    {
        if (false === $clang) {
            $clang = rex_clang::getCurrentId();
        }

        return self::getSlicesWhere(
            'article_id=? AND clang_id=? AND revision=?',
            [$an_article_id, $clang, $revision]
        );
    }

    /**
     * Return all slices for an article that have a certain
     * module type.
     *
     * @param int      $an_article_id
     * @param int      $a_moduletype_id
     * @param bool|int $clang
     * @param int      $revision
     *
     * @return self[]
     */
    public static function getSlicesForArticleOfType($an_article_id, $a_moduletype_id, $clang = false, $revision = 0)
    {
        if (false === $clang) {
            $clang = rex_clang::getCurrentId();
        }

        return self::getSlicesWhere(
            'article_id=? AND clang_id=? AND module_id=? AND revision=?',
            [$an_article_id, $clang, $a_moduletype_id, $revision]
        );
    }

    /**
     * Return the next slice for this article.
     *
     * @return self|null
     */
    public function getNextSlice()
    {
        return self::getSliceWhere(
            'priority = ? AND article_id=? AND clang_id = ? AND ctype_id = ? AND revision=?',
            [$this->_priority + 1, $this->_article_id, $this->_clang, $this->_ctype, $this->_revision]
        );
    }

    /**
     * @return self|null
     */
    public function getPreviousSlice()
    {
        return self::getSliceWhere(
            'priority = ? AND article_id=? AND clang_id = ? AND ctype_id = ? AND revision=?',
            [$this->_priority - 1, $this->_article_id, $this->_clang, $this->_ctype, $this->_revision]
        );
    }

    /**
     * Gibt den Slice formatiert zurück.
     *
     * @since 4.1 - 29.05.2008
     * @see rex_article_content::getSlice()
     *
     * @return string
     */
    public function getSlice()
    {
        $art = new rex_article_content();
        $art->setArticleId($this->getArticleId());
        $art->setClang($this->getClangId());
        $art->setSliceRevision($this->getRevision());
        return $art->getSlice($this->getId());
    }

    /**
     * @param string $where
     *
     * @return self|null
     */
    protected static function getSliceWhere($where, array $params = [])
    {
        $slices = self::getSlicesWhere($where, $params);
        return $slices[0] ?? null;
    }

    /**
     * @param string $where
     *
     * @return self[]
     */
    protected static function getSlicesWhere($where, array $params = [])
    {
        $sql = rex_sql::factory();
        // $sql->setDebug();
        $query = '
            SELECT *
            FROM ' . rex::getTable('article_slice') . '
            WHERE ' . $where . '
            ORDER BY ctype_id, priority';

        $sql->setQuery($query, $params);
        $rows = $sql->getRows();
        $slices = [];
        for ($i = 0; $i < $rows; ++$i) {
            $slices[] = new self(
                $sql->getValue('id'),
                $sql->getValue('article_id'),
                $sql->getValue('clang_id'),
                $sql->getValue('ctype_id'),
                $sql->getValue('module_id'),
                $sql->getValue('priority'),
                $sql->getDateTimeValue('createdate'),
                $sql->getDateTimeValue('updatedate'),
                $sql->getValue('createuser'),
                $sql->getValue('updateuser'),
                $sql->getValue('revision'),
                [
                    $sql->getValue('value1'),
                    $sql->getValue('value2'),
                    $sql->getValue('value3'),
                    $sql->getValue('value4'),
                    $sql->getValue('value5'),
                    $sql->getValue('value6'),
                    $sql->getValue('value7'),
                    $sql->getValue('value8'),
                    $sql->getValue('value9'),
                    $sql->getValue('value10'),
                    $sql->getValue('value11'),
                    $sql->getValue('value12'),
                    $sql->getValue('value13'),
                    $sql->getValue('value14'),
                    $sql->getValue('value15'),
                    $sql->getValue('value16'),
                    $sql->getValue('value17'),
                    $sql->getValue('value18'),
                    $sql->getValue('value19'),
                    $sql->getValue('value20'),
                ],
                [
                    $sql->getValue('media1'),
                    $sql->getValue('media2'),
                    $sql->getValue('media3'),
                    $sql->getValue('media4'),
                    $sql->getValue('media5'),
                    $sql->getValue('media6'),
                    $sql->getValue('media7'),
                    $sql->getValue('media8'),
                    $sql->getValue('media9'),
                    $sql->getValue('media10'),
                ],
                [
                    $sql->getValue('medialist1'),
                    $sql->getValue('medialist2'),
                    $sql->getValue('medialist3'),
                    $sql->getValue('medialist4'),
                    $sql->getValue('medialist5'),
                    $sql->getValue('medialist6'),
                    $sql->getValue('medialist7'),
                    $sql->getValue('medialist8'),
                    $sql->getValue('medialist9'),
                    $sql->getValue('medialist10'),
                ],
                [
                    $sql->getValue('link1'),
                    $sql->getValue('link2'),
                    $sql->getValue('link3'),
                    $sql->getValue('link4'),
                    $sql->getValue('link5'),
                    $sql->getValue('link6'),
                    $sql->getValue('link7'),
                    $sql->getValue('link8'),
                    $sql->getValue('link9'),
                    $sql->getValue('link10'),
                ],
                [
                    $sql->getValue('linklist1'),
                    $sql->getValue('linklist2'),
                    $sql->getValue('linklist3'),
                    $sql->getValue('linklist4'),
                    $sql->getValue('linklist5'),
                    $sql->getValue('linklist6'),
                    $sql->getValue('linklist7'),
                    $sql->getValue('linklist8'),
                    $sql->getValue('linklist9'),
                    $sql->getValue('linklist10'),
                ]
            );

            $sql->next();
        }
        return $slices;
    }

    /**
     * @return rex_article
     */
    public function getArticle()
    {
        $article = rex_article::get($this->getArticleId());

        if (!$article) {
            throw new LogicException(sprintf('Article with id=%d not found.', $this->getArticleId()));
        }

        return $article;
    }

    public function getArticleId()
    {
        return $this->_article_id;
    }

    public function getClangId()
    {
        return $this->_clang;
    }

    /**
     * @deprecated since redaxo 5.6, use getClangId() instead
     */
    public function getClang()
    {
        return $this->_clang;
    }

    public function getCtype()
    {
        return $this->_ctype;
    }

    public function getRevision()
    {
        return $this->_revision;
    }

    public function getModuleId()
    {
        return $this->_module_id;
    }

    public function getId()
    {
        return $this->_id;
    }

    public function getValue($index)
    {
        if (is_int($index)) {
            return $this->_values[$index - 1];
        }

        $attrName = '_' . $index;
        if (isset($this->$attrName)) {
            return $this->$attrName;
        }

        return null;
    }

    public function getLink($index)
    {
        return $this->_links[$index - 1];
    }

    public function getLinkUrl($index)
    {
        return rex_getUrl($this->getLink($index));
    }

    public function getLinkList($index)
    {
        return $this->_linklists[$index - 1];
    }

    public function getMedia($index)
    {
        return $this->_media[$index - 1];
    }

    /**
     * @return string
     */
    public function getMediaUrl($index)
    {
        return rex_url::media($this->getMedia($index));
    }

    public function getMediaList($index)
    {
        return $this->_medialists[$index - 1];
    }

    public function getPriority()
    {
        return $this->_priority;
    }
}
