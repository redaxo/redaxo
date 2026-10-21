<?php

namespace Redaxo\Core\Content;

use Redaxo\Core\Database\Sql;
use Redaxo\Core\Form\Reference;

/**
 * The media and articles referenced by the module fields of the slices, kept in `rex_article_slice_reference` to
 * find where an item is in use.
 *
 * @internal
 */
final class ArticleSliceReference
{
    private const string TABLE = 'rex_article_slice_reference';

    private function __construct() {}

    /** Rewrites the references of the slice from its stored field values. */
    public static function update(ArticleSlice $slice): void
    {
        $sql = Sql::factory();
        $sql->setQuery('DELETE FROM ' . self::TABLE . ' WHERE slice_id = ?', [$slice->id]);

        if (!Module::exists($slice->moduleKey)) {
            return;
        }

        $references = Module::of($slice)->references();
        if (!$references) {
            return;
        }

        $sql->setTable(self::TABLE);
        foreach ($references as $reference) {
            $sql->addRecord(static function (Sql $record) use ($slice, $reference): void {
                $record->setValue('slice_id', $slice->id);
                $record->setValue('type', $reference->type);
                $record->setValue('target', $reference->target);
            });
        }
        $sql->insert();
    }

    /** Copies the references of a slice to its copy, which holds the same field values. */
    public static function copy(int $fromSliceId, int $toSliceId): void
    {
        Sql::factory()->setQuery(
            'INSERT INTO ' . self::TABLE . ' (slice_id, type, target) SELECT ?, type, target FROM ' . self::TABLE . ' WHERE slice_id = ?',
            [$toSliceId, $fromSliceId],
        );
    }

    /**
     * Returns the articles (in all languages and revisions) having a slice that references the given item.
     *
     * @return list<array{articleId: int, languageId: int}>
     */
    public static function findArticles(Reference $reference): array
    {
        $sql = Sql::factory();
        $sql->setQuery('
            SELECT DISTINCT s.article_id, s.language_id
            FROM ' . self::TABLE . ' r
            JOIN rex_article_slice s ON s.id = r.slice_id
            WHERE r.type = ? AND r.target = ?
            ORDER BY s.article_id, s.language_id', [$reference->type, $reference->target]);

        $articles = [];
        foreach ($sql as $row) {
            $articles[] = ['articleId' => $row->getIntValue('article_id'), 'languageId' => $row->getIntValue('language_id')];
        }

        return $articles;
    }

    /**
     * Rewrites the references of all slices, e.g. after the fields of a module have changed.
     *
     * @return int the number of slices
     */
    public static function rebuild(): int
    {
        $sql = Sql::factory();
        $sql->setQuery('SELECT * FROM rex_article_slice');

        $count = 0;
        foreach ($sql as $row) {
            self::update(ArticleSlice::fromSql($row));
            ++$count;
        }

        return $count;
    }
}
