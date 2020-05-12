<?php

/**
 * @author dergel
 *
 * @package redaxo\structure\history
 */
class rex_article_slice_history
{
    /**
     * @return string
     */
    public static function getTable()
    {
        return rex::getTablePrefix() . 'article_slice_history';
    }

    /*
     * Only Snapshots from LiveVersion.
     */

    public static function makeSnapshot($article_id, $clang_id, $history_type)
    {
        self::checkTables();

        $slices = rex_sql::factory()->getArray(
            'select * from ' . rex::getTable('article_slice') . ' where article_id=? and clang_id=? and revision=?',
            [
                $article_id,
                $clang_id,
                0,
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

    /**
     * @return array
     */
    public static function getSnapshots($article_id, $clang_id)
    {
        return rex_sql::factory()->getArray(
        'select distinct history_date, history_type, history_user from ' . self::getTable() . ' where article_id=? and clang_id=? and revision=? order by history_date desc',
        [$article_id, $clang_id, 0]
        );
    }

    /**
     * @return bool
     */
    public static function restoreSnapshot($history_date, $article_id, $clang_id)
    {
        self::checkTables();

        $sql = rex_sql::factory();
        $slices = $sql->getArray('select id from ' . self::getTable() . ' where article_id=? and clang_id=? and revision=? and history_date=?', [$article_id, $clang_id, 0, $history_date]);

        if (0 == count($slices)) {
            return false;
        }

        self::makeSnapshot($article_id, $clang_id, 'version set ' . $history_date);

        $article_slices_table = rex_sql_table::get(rex::getTable('article_slice'));

        $sql = rex_sql::factory();
        $sql->setQuery('delete from ' . rex::getTable('article_slice') . ' where article_id=? and clang_id=? and revision=?', [$article_id, $clang_id, 0]);

        $slices = rex_sql::factory();
        $slices = $slices->getArray('select * from ' . self::getTable() . ' where article_id=? and clang_id=? and revision=? and history_date=?', [$article_id, $clang_id, 0, $history_date]);

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

    public static function clearHistoryByDate(DateTimeInterface $deleteDate): void
    {
        rex_sql::factory()->setQuery('delete from ' . self::getTable() .' where history_date < ?', [$deleteDate->format('Y-m-d H:i:s')]);
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
