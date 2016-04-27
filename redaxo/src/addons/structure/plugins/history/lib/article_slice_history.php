<?php

class rex_article_slice_history
{
    public static function getTable()
    {
        return rex::getTablePrefix() . 'article_slice_history';
    }

    public static function makeArticleSlicesSnapshot($article_id, $clang_id, $history_type = '', $revision = 0)
    {
        self::checkTables();

        $slices = rex_sql::factory();
        $slices = $slices->getArray('select * from ' . rex::getTable('article_slice') . ' where article_id=? and clang_id=? and revision=?',
            [
                $article_id,
                $clang_id,
                $revision,
            ]);

        $microtime = explode(',', microtime(true));
        $history_date = date('Y-m-d H:i:s ') . $microtime[1];

        foreach ($slices as $slice) {
            $sql = rex_sql::factory();
            $sql->setTable(self::getTable());
            foreach ($slice as $k => $v) {
                if ($k == 'id') {
                    $sql->setValue('slice_id', $v);
                } else {
                    $sql->setValue($k, $v);
                }
            }
            $sql->setValue('history_type', $history_type);
            $sql->setValue('history_date', $history_date);
            $sql->insert();
        }
    }

    public static function getVersionsByDate($article_id, $clang_id, $revision = 0)
    {
        $versions = rex_sql::factory()->getArray('select distinct history_date,updateuser from ' . self::getTable() . ' where article_id=? and clang_id=? and revision=? order by history_date desc', [$article_id, $clang_id, $revision]);
        return $versions;
    }

    public static function setVersionByDate($history_date, $article_id, $clang_id, $revision = 0)
    {
        self::checkTables();

        $sql = rex_sql::factory();
        $slices = $sql->getArray('select id from ' . self::getTable() . ' where article_id=? and clang_id=? and revision=? and history_date=?', [$article_id, $clang_id, $revision, $history_date]);

        if (count($slices) == 0) {
            return false;
        }

        self::makeArticleSlicesSnapshot($article_id, $clang_id, 'version set ' . $history_date, $revision);

        $sql = rex_sql::factory();
        $sql->setQuery('delete from ' . rex::getTable('article_slice') . ' where article_id=? and clang_id=? and revision=?', [$article_id, $clang_id, $revision]);

        $slices = rex_sql::factory();
        $slices = $slices->getArray('select * from ' . self::getTable() . ' where article_id=? and clang_id=? and revision=? and history_date=?', [$article_id, $clang_id, $revision, $history_date]);

        foreach ($slices as $slice) {
            $sql = rex_sql::factory();
            $sql->setTable(rex::getTable('article_slice'));

            $ignore_fields = ['id', 'slice_id', 'history_date', 'history_type'];
            foreach ($slice as $k => $v) {
                if (in_array($k, $ignore_fields)) {
                } else {
                    $sql->setValue($k, $v);
                }
            }
            $sql->insert();
        }

        rex_article_cache::delete($article_id, $clang_id);

        return true;
    }

    public static function clearAllHistory()
    {
        rex_sql::factory()->setQuery('delete from ' . self::getTable() . '', []);
    }

    public static function checkTables()
    {
        $slices_table = rex_sql_table::get(rex::getTable('article_slice'));
        $history_table = rex_sql_table::get(self::getTable());

        echo "\n\n\n\n";
        foreach ($slices_table->getColumns() as $column) {
            if (strtolower($column->getName()) != 'id') {
                echo "\n** " . $column->getName();
                $history_table->ensureColumn($column)->alter();
            }
        }
    }
}
