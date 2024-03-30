<?php

use Redaxo\Core\Core;
use Redaxo\Core\Database\Sql;
use Redaxo\Core\Structure\ArticleCache;

class rex_article_revision
{
    public const LIVE = 0; // live revision
    public const WORK = 1; // working copy

    /**
     * @param int $articleId
     * @param int $clang
     * @param self::LIVE|self::WORK $fromRevisionId
     * @param self::LIVE|self::WORK $toRevisionId
     *
     * @return bool
     */
    public static function copyContent($articleId, $clang, $fromRevisionId, $toRevisionId)
    {
        if ($fromRevisionId == $toRevisionId) {
            return false;
        }

        // clear the revision to which we will later copy all slices
        $dc = Sql::factory();
        // $dc->setDebug();
        $dc->setQuery('delete from ' . Core::getTablePrefix() . 'article_slice where article_id=? and clang_id=? and revision=?', [$articleId, $clang, $toRevisionId]);

        $gc = Sql::factory();
        $gc->setQuery('select * from ' . Core::getTablePrefix() . 'article_slice where article_id=? and clang_id=? and revision=? ORDER by ctype_id, priority', [$articleId, $clang, $fromRevisionId]);

        $cols = Sql::factory();
        $cols->setQuery('SHOW COLUMNS FROM ' . Core::getTablePrefix() . 'article_slice');
        foreach ($gc as $slice) {
            $ins = Sql::factory();
            // $ins->setDebug();
            $ins->setTable(Core::getTablePrefix() . 'article_slice');

            foreach ($cols as $col) {
                $colname = (string) $col->getValue('Field');
                $ins->setValue($colname, $slice->getValue($colname));
            }

            $ins->setValue('id', 0); // trigger auto increment
            $ins->setValue('revision', $toRevisionId);
            $ins->addGlobalCreateFields();
            $ins->addGlobalUpdateFields();
            $ins->insert();
        }

        ArticleCache::delete($articleId);
        return true;
    }

    /**
     * @param int $articleId
     * @param int $clang
     * @param self::WORK $fromRevisionId
     *
     * @return true
     */
    public static function clearContent($articleId, $clang, $fromRevisionId)
    {
        if (self::WORK != $fromRevisionId) {
            throw new InvalidArgumentException(sprintf('Revision "%s" can not be cleared, only the working version (%d).', $fromRevisionId, self::WORK));
        }

        $dc = Sql::factory();
        // $dc->setDebug();
        $dc->setQuery('delete from ' . Core::getTablePrefix() . 'article_slice where article_id=? and clang_id=? and revision=?', [$articleId, $clang, $fromRevisionId]);

        return true;
    }

    /** @param self::LIVE|self::WORK $revision */
    public static function setSessionArticleRevision(int $articleId, int $revision): void
    {
        $login = Core::getProperty('login');
        /** @var array<int, self::LIVE|self::WORK>|null $revisions */
        $revisions = $login->getSessionVar('rex_version_article', []);
        $revisions = is_array($revisions) ? $revisions : [];

        $revisions[$articleId] = $revision;
        $login->setSessionVar('rex_version_article', $revisions);
    }

    /** @return self::LIVE|self::WORK */
    public static function getSessionArticleRevision(int $articleId): int
    {
        /** @var array<int, self::LIVE|self::WORK> $revisions */
        $revisions = Core::getProperty('login')->getSessionVar('rex_version_article', []);

        return (int) ($revisions[$articleId] ?? self::WORK);
    }
}
