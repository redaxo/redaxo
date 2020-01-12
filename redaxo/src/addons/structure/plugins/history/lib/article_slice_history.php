<?php

/**
 * @author dergel
 *
 * @package redaxo\structure\history
 */
class rex_article_slice_history
{
    public static function getTable()
    {
        return rex::getTablePrefix() . 'article_slice_history';
    }

    public static function makeSnapshot($article_id, $clang_id, $history_type, $revision = 0)
    {
        self::checkTables();

        $slices = rex_sql::factory()->getArray(
            'select * from ' . rex::getTable('article_slice') . ' where article_id=? and clang_id=? and revision=?',
            [
                $article_id,
                $clang_id,
                $revision,
            ]
        );

        $history_date = date('Y-m-d H:i:s');

        foreach ($slices as $slice) {
            $sql = rex_sql::factory();
            $sql->setTable(self::getTable());
            foreach ($slice as $k => $v) {
                if ('id' == $k) {
                    $sql->setValue('slice_id', $v);
                } else {
                    $sql->setValue($k, $v);
                }
            }
            $sql->setValue('history_type', $history_type);
            $sql->setValue('history_date', $history_date);
            $sql->setValue('history_user', rex::getUser()->getValue('login'));
            $sql->insert();
        }
    }

    public static function getSnapshots($article_id, $clang_id, $revision = 0)
    {
        return rex_sql::factory()->getArray(
        'select distinct history_date, history_type, history_user from ' . self::getTable() . ' where article_id=? and clang_id=? and revision=? order by history_date desc',
        [$article_id, $clang_id, $revision]
        );
    }

    public static function restoreSnapshot($history_date, $article_id, $clang_id, $revision = 0)
    {
        self::checkTables();

        $sql = rex_sql::factory();
        $slices = $sql->getArray('select id from ' . self::getTable() . ' where article_id=? and clang_id=? and revision=? and history_date=?', [$article_id, $clang_id, $revision, $history_date]);

        if (0 == count($slices)) {
            return false;
        }

        self::makeSnapshot($article_id, $clang_id, 'version set ' . $history_date, $revision);

        $article_slices_table = rex_sql_table::get(rex::getTable('article_slice'));

        $sql = rex_sql::factory();
        $sql->setQuery('delete from ' . rex::getTable('article_slice') . ' where article_id=? and clang_id=? and revision=?', [$article_id, $clang_id, $revision]);

        $slices = rex_sql::factory();
        $slices = $slices->getArray('select * from ' . self::getTable() . ' where article_id=? and clang_id=? and revision=? and history_date=?', [$article_id, $clang_id, $revision, $history_date]);

        foreach ($slices as $slice) {
            $sql = rex_sql::factory();
            $sql->setTable(rex::getTable('article_slice'));

            $ignoreFields = ['id', 'slice_id', 'history_date', 'history_type', 'history_user'];
            foreach ($article_slices_table->getColumns() as $column) {
                $columnName = $column->getName();
                if (!in_array($columnName, $ignoreFields)) {
                    $sql->setValue($columnName, $slice[$columnName]);
                }
            }

            $sql->insert();
        }
        rex_article_cache::delete($article_id, $clang_id);
        return true;
    }

    public static function clearAllHistory()
    {
        rex_sql::factory()->setQuery('delete from ' . self::getTable());
    }

    public static function checkTables()
    {
        $slices_table = rex_sql_table::get(rex::getTable('article_slice'));
        $history_table = rex_sql_table::get(self::getTable());

        foreach ($slices_table->getColumns() as $column) {
            if ('id' != strtolower($column->getName())) {
                $history_table->ensureColumn($column);
            }
        }

        $history_table->alter();
    }
}
