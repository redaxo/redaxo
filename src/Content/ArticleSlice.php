<?php

namespace Redaxo\Core\Content;

use Redaxo\Core\Content\Exception\ArticleNotFoundException;
use Redaxo\Core\Core;
use Redaxo\Core\Database\Sql;
use Redaxo\Core\Exception\LogicException;
use Redaxo\Core\Language\Language;

use function is_array;
use function json_decode;
use function json_encode;
use function sprintf;

use const JSON_PRESERVE_ZERO_FRACTION;
use const JSON_THROW_ON_ERROR;
use const JSON_UNESCAPED_SLASHES;
use const JSON_UNESCAPED_UNICODE;

final readonly class ArticleSlice
{
    private const string ORDER_ASC = 'ASC';
    private const string ORDER_DESC = 'DESC';

    /** @param array<string, mixed> $data */
    private function __construct(
        public int $id,
        public int $articleId,
        public int $languageId,
        public int $contentSectionId,
        public string $moduleKey,
        public int $priority,
        public int $status,
        public int $createdate,
        public int $updatedate,
        public string $createuser,
        public string $updateuser,
        public int $revision,
        /**
         * The stored values of the module fields, keyed by storage key; read them typed via {@see Module::of()}.
         *
         * @internal
         */
        public array $data,
    ) {}

    /** @internal  */
    public static function forNewSlice(
        int $articleId,
        int $languageId,
        int $ctype,
        string $moduleKey,
        int $priority,
        int $revision,
    ): self {
        return new self(
            0,
            $articleId,
            $languageId,
            $ctype,
            $moduleKey,
            $priority,
            1,
            $time = time(),
            $time,
            $user = Core::requireUser()->login,
            $user,
            $revision,
            [],
        );
    }

    /** @internal  */
    public static function fromSql(Sql $sql): self
    {
        $table = 'rex_article_slice';

        return new self(
            $sql->getIntValue($table . '.id'),
            $sql->getIntValue($table . '.article_id'),
            $sql->getIntValue($table . '.language_id'),
            $sql->getIntValue($table . '.ctype_id'),
            $sql->getStringValue($table . '.module'),
            $sql->getIntValue($table . '.priority'),
            $sql->getIntValue($table . '.status'),
            (int) $sql->getDateTimeValue($table . '.createdate'),
            (int) $sql->getDateTimeValue($table . '.updatedate'),
            $sql->getStringValue($table . '.createuser'),
            $sql->getStringValue($table . '.updateuser'),
            $sql->getIntValue($table . '.revision'),
            self::decodeData($sql->getNullableStringValue($table . '.data')),
        );
    }

    /**
     * Encodes the stored values of the module fields for the `data` column; `null` for no values.
     *
     * @param array<string, mixed> $data
     * @internal
     */
    public static function encodeData(array $data): ?string
    {
        return $data ? json_encode($data, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRESERVE_ZERO_FRACTION) : null;
    }

    /** @return array<string, mixed> */
    private static function decodeData(?string $json): array
    {
        /** @var mixed $data */
        $data = null === $json ? null : json_decode($json, true);

        /** @var array<string, mixed> */
        return is_array($data) ? $data : [];
    }

    public static function getArticleSliceById(int $id, ?int $languageId = null, int $revision = 0): ?self
    {
        $languageId ??= Language::getCurrentId();

        return self::getSliceWhere(
            'id=? AND language_id=? and revision=?',
            [$id, $languageId, $revision],
        );
    }

    /**
     * Return the first slice for an article.
     * This can then be used to iterate over all the slices in the order as they appear using the getNextSlice() function.
     */
    public static function getFirstSliceForArticle(int $articleId, ?int $languageId = null, int $revision = 0, bool $ignoreOfflines = false): ?self
    {
        $languageId ??= Language::getCurrentId();

        foreach (range(1, 20) as $ctype) {
            $slice = self::getFirstSliceForCtype($ctype, $articleId, $languageId, $revision, $ignoreOfflines);
            if (null !== $slice) {
                return $slice;
            }
        }

        return null;
    }

    /** Returns the first slice of the given ctype of an article. */
    public static function getFirstSliceForCtype(int $ctype, int $articleId, ?int $languageId = null, int $revision = 0, bool $ignoreOfflines = false): ?self
    {
        $languageId ??= Language::getCurrentId();

        return self::getSliceWhere(
            'article_id=? AND language_id=? AND ctype_id=? AND priority=1 AND revision=?' . ($ignoreOfflines ? ' AND status = 1' : ''),
            [$articleId, $languageId, $ctype, $revision],
        );
    }

    /**
     * Return all slices for an article that have a certain
     * language or revision.
     *
     * @return list<self>
     */
    public static function getSlicesForArticle(int $articleId, ?int $languageId = null, int $revision = 0, bool $ignoreOfflines = false): array
    {
        $languageId ??= Language::getCurrentId();

        return self::getSlicesWhere(
            'article_id=? AND language_id=? AND revision=?' . ($ignoreOfflines ? ' AND status = 1' : ''),
            [$articleId, $languageId, $revision],
        );
    }

    /**
     * Return all slices for an article that have a certain module type.
     *
     * @return list<self>
     */
    public static function getSlicesForArticleOfType(int $articleId, string $moduleKey, ?int $languageId = null, int $revision = 0, bool $ignoreOfflines = false): array
    {
        $languageId ??= Language::getCurrentId();

        return self::getSlicesWhere(
            'article_id=? AND language_id=? AND module=? AND revision=?' . ($ignoreOfflines ? ' AND status = 1' : ''),
            [$articleId, $languageId, $moduleKey, $revision],
        );
    }

    public function getNextSlice(bool $ignoreOfflines = false): ?self
    {
        return self::getSliceWhere(
            'priority ' . ($ignoreOfflines ? '>=' : '=') . ' ? AND article_id=? AND language_id = ? AND ctype_id = ? AND revision=?' . ($ignoreOfflines ? ' AND status = 1' : ''),
            [$this->priority + 1, $this->articleId, $this->languageId, $this->contentSectionId, $this->revision],
        );
    }

    public function getPreviousSlice(bool $ignoreOfflines = false): ?self
    {
        return self::getSliceWhere(
            'priority ' . ($ignoreOfflines ? '<=' : '=') . ' ? AND article_id=? AND language_id = ? AND ctype_id = ? AND revision=?' . ($ignoreOfflines ? ' AND status = 1' : ''),
            [$this->priority - 1, $this->articleId, $this->languageId, $this->contentSectionId, $this->revision],
            self::ORDER_DESC,
        );
    }

    /**
     * Returns the rendered content of this slice.
     *
     * @see ArticleContent::renderSlice()
     *
     * @throws ArticleNotFoundException
     */
    public function renderSlice(): string
    {
        $art = new ArticleContent($this->articleId, $this->languageId);
        $art->sliceRevision = $this->revision;
        return $art->renderSlice($this->id);
    }

    /**
     * @param literal-string $where
     * @param array<scalar|null> $params
     * @param self::ORDER_* $orderDirection
     */
    private static function getSliceWhere(string $where, array $params = [], string $orderDirection = self::ORDER_ASC): ?self
    {
        $slices = self::getSlicesWhere($where, $params, $orderDirection, 1);
        return $slices[0] ?? null;
    }

    /**
     * @param literal-string $where
     * @param array<scalar|null> $params
     * @param self::ORDER_* $orderDirection
     *
     * @return list<self>
     */
    private static function getSlicesWhere(string $where, array $params = [], string $orderDirection = self::ORDER_ASC, ?int $limit = null): array
    {
        $sql = Sql::factory();
        // $sql->setDebug();
        $query = '
            SELECT *
            FROM rex_article_slice
            WHERE ' . $where . '
            ORDER BY ctype_id ' . $orderDirection . ', priority ' . $orderDirection;

        if (null !== $limit) {
            $query .= ' LIMIT ' . $limit;
        }

        $sql->setQuery($query, $params);
        $rows = $sql->getRows();
        $slices = [];
        for ($i = 0; $i < $rows; ++$i) {
            $slices[] = self::fromSql($sql);

            $sql->next();
        }
        return $slices;
    }

    public function getArticle(): Article
    {
        $article = Article::get($this->articleId);

        if (!$article) {
            throw new LogicException(sprintf('Article with id=%d not found.', $this->articleId));
        }

        return $article;
    }

    public function isOnline(): bool
    {
        return 1 === $this->status;
    }

    /**
     * @internal
     * @param array{id: int, articleId: int, languageId: int, contentSectionId: int, moduleKey: string, priority: int, status: int, createdate: int, updatedate: int, createuser: string, updateuser: string, revision: int, data: array<string, mixed>} $data
     */
    public static function __set_state(array $data): self
    {
        return new self(
            $data['id'],
            $data['articleId'],
            $data['languageId'],
            $data['contentSectionId'],
            $data['moduleKey'],
            $data['priority'],
            $data['status'],
            $data['createdate'],
            $data['updatedate'],
            $data['createuser'],
            $data['updateuser'],
            $data['revision'],
            $data['data'],
        );
    }
}
