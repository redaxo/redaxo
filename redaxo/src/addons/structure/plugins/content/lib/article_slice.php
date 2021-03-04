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
    protected const ORDER_ASC = 'ASC';
    protected const ORDER_DESC = 'DESC';

    private $id;
    private $articleId;
    private $clang;
    private $ctype;
    private $priority;
    private $status;
    private $moduleId;

    private $createdate;
    private $updatedate;
    private $createuser;
    private $updateuser;
    private $revision;

    private $values;
    private $media;
    private $medialists;
    private $links;
    private $linklists;

    /**
     * Constructor.
     *
     * @param int    $id
     * @param int    $articleId
     * @param int    $clang
     * @param int    $ctype
     * @param int    $moduleId
     * @param int    $priority
     * @param int    $status
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
        $id, $articleId, $clang, $ctype, $moduleId, $priority, $status,
        $createdate, $updatedate, $createuser, $updateuser, $revision,
        $values, $media, $medialists, $links, $linklists)
    {
        $this->id = $id;
        $this->articleId = $articleId;
        $this->clang = $clang;
        $this->ctype = $ctype;
        $this->priority = $priority;
        $this->status = $status;
        $this->moduleId = $moduleId;

        $this->createdate = $createdate;
        $this->updatedate = $updatedate;
        $this->createuser = $createuser;
        $this->updateuser = $updateuser;
        $this->revision = $revision;

        $this->values = $values;
        $this->media = $media;
        $this->medialists = $medialists;
        $this->links = $links;
        $this->linklists = $linklists;
    }

    /**
     * Return an ArticleSlice by its id.
     *
     * @param int      $anId
     * @param bool|int $clang
     * @param int      $revision
     *
     * @return self|null
     */
    public static function getArticleSliceById($anId, $clang = false, $revision = 0)
    {
        if (false === $clang) {
            $clang = rex_clang::getCurrentId();
        }

        return self::getSliceWhere(
            'id=? AND clang_id=? and revision=?',
            [$anId, $clang, $revision]
        );
    }

    /**
     * Return the first slice for an article.
     * This can then be used to iterate over all the
     * slices in the order as they appear using the
     * getNextSlice() function.
     *
     * @param int      $anArticleId
     * @param bool|int $clang
     * @param int      $revision
     * @param bool     $ignoreOfflines
     *
     * @return self|null
     */
    public static function getFirstSliceForArticle($anArticleId, $clang = false, $revision = 0, $ignoreOfflines = false)
    {
        if (false === $clang) {
            $clang = rex_clang::getCurrentId();
        }

        foreach (range(1, 20) as $ctype) {
            $slice = self::getFirstSliceForCtype($ctype, $anArticleId, $clang, $revision, $ignoreOfflines);
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
     * @param int      $anArticleId
     * @param bool|int $clang
     * @param int      $revision
     * @param bool     $ignoreOfflines
     *
     * @return self|null
     */
    public static function getFirstSliceForCtype($ctype, $anArticleId, $clang = false, $revision = 0, $ignoreOfflines = false)
    {
        if (false === $clang) {
            $clang = rex_clang::getCurrentId();
        }

        return self::getSliceWhere(
            'article_id=? AND clang_id=? AND ctype_id=? AND priority=1 AND revision=?'.($ignoreOfflines ? ' AND status = 1' : ''),
            [$anArticleId, $clang, $ctype, $revision]
        );
    }

    /**
     * Return all slices for an article that have a certain
     * clang or revision.
     *
     * @param int      $anArticleId
     * @param bool|int $clang
     * @param int      $revision
     * @param bool     $ignoreOfflines
     *
     * @return self[]
     */
    public static function getSlicesForArticle($anArticleId, $clang = false, $revision = 0, $ignoreOfflines = false)
    {
        if (false === $clang) {
            $clang = rex_clang::getCurrentId();
        }

        return self::getSlicesWhere(
            'article_id=? AND clang_id=? AND revision=?'.($ignoreOfflines ? ' AND status = 1' : ''),
            [$anArticleId, $clang, $revision]
        );
    }

    /**
     * Return all slices for an article that have a certain
     * module type.
     *
     * @param int      $anArticleId
     * @param int      $aModuletypeId
     * @param bool|int $clang
     * @param int      $revision
     * @param bool     $ignoreOfflines
     *
     * @return self[]
     */
    public static function getSlicesForArticleOfType($anArticleId, $aModuletypeId, $clang = false, $revision = 0, $ignoreOfflines = false)
    {
        if (false === $clang) {
            $clang = rex_clang::getCurrentId();
        }

        return self::getSlicesWhere(
            'article_id=? AND clang_id=? AND module_id=? AND revision=?'.($ignoreOfflines ? ' AND status = 1' : ''),
            [$anArticleId, $clang, $aModuletypeId, $revision]
        );
    }

    /**
     * Return the next slice for this article.
     *
     * @param bool $ignoreOfflines
     *
     * @return self|null
     */
    public function getNextSlice($ignoreOfflines = false)
    {
        return self::getSliceWhere(
            'priority '.($ignoreOfflines ? '>=' : '=').' ? AND article_id=? AND clang_id = ? AND ctype_id = ? AND revision=?'.($ignoreOfflines ? ' AND status = 1' : ''),
            [$this->priority + 1, $this->articleId, $this->clang, $this->ctype, $this->revision]
        );
    }

    /**
     * @param bool $ignoreOfflines
     *
     * @return self|null
     */
    public function getPreviousSlice($ignoreOfflines = false)
    {
        return self::getSliceWhere(
            'priority '.($ignoreOfflines ? '<=' : '=').' ? AND article_id=? AND clang_id = ? AND ctype_id = ? AND revision=?'.($ignoreOfflines ? ' AND status = 1' : ''),
            [$this->priority - 1, $this->articleId, $this->clang, $this->ctype, $this->revision],
            self::ORDER_DESC
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
     * @psalm-param self::ORDER_* $orderDirection
     *
     * @return self|null
     */
    protected static function getSliceWhere($where, array $params = [], string $orderDirection = self::ORDER_ASC)
    {
        $slices = self::getSlicesWhere($where, $params, $orderDirection, 1);
        return $slices[0] ?? null;
    }

    /**
     * @param string $where
     * @psalm-param self::ORDER_* $orderDirection
     *
     * @return self[]
     */
    protected static function getSlicesWhere($where, array $params = [], string $orderDirection = 'ASC', ?int $limit = null)
    {
        $sql = rex_sql::factory();
        // $sql->setDebug();
        $query = '
            SELECT *
            FROM ' . rex::getTable('article_slice') . '
            WHERE ' . $where . '
            ORDER BY ctype_id '.$orderDirection.', priority '.$orderDirection;

        if (null !== $limit) {
            $query .= ' LIMIT '.$limit;
        }

        $sql->setQuery($query, $params);
        $rows = $sql->getRows();
        $slices = [];
        for ($i = 0; $i < $rows; ++$i) {
            $slices[] = new self(
                (int) $sql->getValue('id'),
                (int) $sql->getValue('article_id'),
                (int) $sql->getValue('clang_id'),
                (int) $sql->getValue('ctype_id'),
                (int) $sql->getValue('module_id'),
                (int) $sql->getValue('priority'),
                (int) $sql->getValue('status'),
                $sql->getDateTimeValue('createdate'),
                $sql->getDateTimeValue('updatedate'),
                $sql->getValue('createuser'),
                $sql->getValue('updateuser'),
                (int) $sql->getValue('revision'),
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
        return $this->articleId;
    }

    public function getClangId()
    {
        return $this->clang;
    }

    /**
     * @deprecated since redaxo 5.6, use getClangId() instead
     */
    public function getClang()
    {
        return $this->clang;
    }

    public function getCtype()
    {
        return $this->ctype;
    }

    public function getRevision()
    {
        return $this->revision;
    }

    public function getModuleId()
    {
        return $this->moduleId;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getValue($index)
    {
        if (is_int($index)) {
            return $this->values[$index - 1];
        }

        if (isset($this->$index)) {
            return $this->$index;
        }

        return null;
    }

    public function getLink($index)
    {
        return $this->links[$index - 1];
    }

    public function getLinkUrl($index)
    {
        return rex_getUrl($this->getLink($index));
    }

    public function getLinkList($index)
    {
        return $this->linklists[$index - 1];
    }

    public function getMedia($index)
    {
        return $this->media[$index - 1];
    }

    /**
     * @param int $index
     *
     * @return string
     */
    public function getMediaUrl($index)
    {
        return rex_url::media($this->getMedia($index));
    }

    public function getMediaList($index)
    {
        return $this->medialists[$index - 1];
    }

    public function getPriority()
    {
        return $this->priority;
    }

    public function isOnline(): bool
    {
        return 1 == $this->status;
    }
}
