<?php

/**
 * @package redaxo\structure\version
 */
class rex_article_revision
{
    public const LIVE = 0; // live revision
    public const WORK = 1; // working copy

    /**
     * @return bool
     */
    public static function copyContent($article_id, $clang, $from_revision_id, $to_revision_id)
    {
        if ($from_revision_id == $to_revision_id) {
            return false;
        }

        // clear the revision to which we will later copy all slices
        $dc = rex_sql::factory();
        // $dc->setDebug();
        $dc->setQuery('delete from ' . rex::getTablePrefix() . 'article_slice where article_id=? and clang_id=? and revision=?', [$article_id, $clang, $to_revision_id]);

        $gc = rex_sql::factory();
        $gc->setQuery('select * from ' . rex::getTablePrefix() . 'article_slice where article_id=? and clang_id=? and revision=? ORDER by ctype_id, priority', [$article_id, $clang, $from_revision_id]);

        $cols = rex_sql::factory();
        $cols->setquery('SHOW COLUMNS FROM ' . rex::getTablePrefix() . 'article_slice');
        foreach ($gc as $slice) {
            $ins = rex_sql::factory();
            // $ins->setDebug();
            $ins->setTable(rex::getTablePrefix() . 'article_slice');

            foreach ($cols as $col) {
                $colname = $col->getValue('Field');
                $ins->setValue($colname, $slice->getValue($colname));
            }

            $ins->setValue('id', 0); // trigger auto increment
            $ins->setValue('revision', $to_revision_id);
            $ins->addGlobalCreateFields();
            $ins->addGlobalUpdateFields();
            $ins->insert();
        }

        rex_article_cache::delete($article_id);
        return true;
    }

    /**
     * @return true
     */
    public static function clearContent($article_id, $clang, $from_revision_id)
    {
        if (self::WORK != $from_revision_id) {
            throw new InvalidArgumentException(sprintf('Revision "%s" can not be cleared, only the working version (%d).', $from_revision_id, self::WORK));
        }

        $dc = rex_sql::factory();
        // $dc->setDebug();
        $dc->setQuery('delete from ' . rex::getTablePrefix() . 'article_slice where article_id=? and clang_id=? and revision=?', [$article_id, $clang, $from_revision_id]);

        return true;
    }
}
