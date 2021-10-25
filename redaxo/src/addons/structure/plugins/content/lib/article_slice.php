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

    /** @var int */
    private $id;

    /** @var int */
    private $articleId;

    /** @var int */
    private $clang;

    /** @var int */
    private $ctype;

    /** @var int */
    private $priority;

    /** @var int */
    private $status;

    /** @var int */
    private $moduleId;

    /** @var int */
    private $createdate;

    /** @var int */
    private $updatedate;

    /** @var string */
    private $createuser;

    /** @var string */
    private $updateuser;

    /** @var int */
    private $revision;

    /** @var array<int, string|null> */
    private $values;

    /** @var array<int, string|null> */
    private $media;

    /** @var array<int, string|null> */
    private $medialists;

    /** @var array<int, string|null> */
    private $links;

    /** @var array<int, string|null> */
    private $linklists;

    /**
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
     * @param array<int, string|null> $values
     * @param array<int, string|null> $media
     * @param array<int, string|null> $medialists
     * @param array<int, string|null> $links
     * @param array<int, string|null> $linklists
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
     * @param false|int $clang
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
     * @param false|int $clang
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
     * @param false|int $clang
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
     * @param false|int $clang
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
     * @param false|int $clang
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
            $data = [];
            foreach (['value' => 20, 'media' => 10, 'medialist' => 10, 'link' => 10, 'linklist' => 10] as $list => $count) {
                for ($k = 1; $k <= $count; ++$k) {
                    $value = $sql->getValue($list.$k);
                    $data[$list][] = null == $value ? null : (string) $value;
                }
            }

            $slices[] = new self(
                (int) $sql->getValue('id'),
                (int) $sql->getValue('article_id'),
                (int) $sql->getValue('clang_id'),
                (int) $sql->getValue('ctype_id'),
                (int) $sql->getValue('module_id'),
                (int) $sql->getValue('priority'),
                (int) $sql->getValue('status'),
                (int) $sql->getDateTimeValue('createdate'),
                (int) $sql->getDateTimeValue('updatedate'),
                (string) $sql->getValue('createuser'),
                (string) $sql->getValue('updateuser'),
                (int) $sql->getValue('revision'),
                $data['value'],
                $data['media'],
                $data['medialist'],
                $data['link'],
                $data['linklist'],
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

    /** @return int */
    public function getArticleId()
    {
        return $this->articleId;
    }

    /** @return int */
    public function getClangId()
    {
        return $this->clang;
    }

    /**
     * @return int
     * @deprecated since redaxo 5.6, use getClangId() instead
     */
    public function getClang()
    {
        return $this->clang;
    }

    /** @return int */
    public function getCtype()
    {
        return $this->ctype;
    }

    /** @return int */
    public function getRevision()
    {
        return $this->revision;
    }

    /** @return int */
    public function getModuleId()
    {
        return $this->moduleId;
    }

    /** @return int */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @template T of int|string
     * @param T $index
     * @return null|int|string
     * @psalm-return (T is int ? string|null : int|string|null)
     */
    public function getValue($index)
    {
        if (is_int($index)) {
            return $this->values[$index - 1];
        }

        if (isset($this->$index)) {
            /** @var string|int */
            return $this->$index;
        }

        return null;
    }

    public function getValueArray(int $index): ?array
    {
        $value = $this->values[$index - 1];

        if (null === $value) {
            return null;
        }

        /** @var mixed $value */
        $value = json_decode($value, true);

        return is_array($value) ? $value : null;
    }

    /**
     * @param int $index
     * @return int|null
     */
    public function getLink($index)
    {
        $link = $this->links[$index - 1];

        return null === $link ? null : (int) $link;
    }

    /**
     * @param int $index
     * @return string|null
     */
    public function getLinkUrl($index)
    {
        $link = $this->getLink($index);

        return null === $link ? null : rex_getUrl($link);
    }

    /**
     * @param int $index
     * @return string|null liefert kommaseparierten String
     */
    public function getLinkList($index)
    {
        return $this->linklists[$index - 1];
    }

    /**
     * @return null|list<int>
     */
    public function getLinkListArray(int $index): ?array
    {
        $list = $this->linklists[$index - 1];

        if (null === $list) {
            return null;
        }

        return array_map('intval', explode(',', $list));
    }

    /**
     * @param int $index
     * @return string|null
     */
    public function getMedia($index)
    {
        return $this->media[$index - 1];
    }

    /**
     * @param int $index
     * @return string|null
     */
    public function getMediaUrl($index)
    {
        $media = $this->getMedia($index);

        return null === $media ? null : rex_url::media($media);
    }

    /**
     * @param int $index
     * @return string|null liefert kommaseparierten String
     */
    public function getMediaList($index)
    {
        return $this->medialists[$index - 1];
    }

    /**
     * @return null|list<string>
     */
    public function getMediaListArray(int $index): ?array
    {
        $list = $this->linklists[$index - 1];

        if (null === $list) {
            return null;
        }

        return explode(',', $list);
    }

    /** @return int */
    public function getPriority()
    {
        return $this->priority;
    }

    public function isOnline(): bool
    {
        return 1 == $this->status;
    }
}
