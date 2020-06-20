<?php

class rex_analytics_webvitals {

    /**
     * Calculates the 95th percentile of all metrics per uri.
     *
     * 95th percentile = value that is bigger than 95 percent of all values.
     *
     * @throws rex_sql_exception
     */
    public function condense()
    {
        $sql = rex_sql::factory();

        $metrics95 = [];
        foreach (['cls', 'fid', 'lcp', 'ttfb'] as $metric) {
            $sql->setQuery(
                "SELECT
                CAST(SUBSTRING_INDEX(SUBSTRING_INDEX( GROUP_CONCAT(".$metric." ORDER BY
                ".$metric." SEPARATOR ','), ',', 95/100 * COUNT(*) + 1), ',', -1) AS DECIMAL)
                AS 95thPer, uri, article_id, clang_id
                FROM ".rex::getTable('webvitals')."
                GROUP BY uri"
            );
            foreach ($sql as $row) {
                if (!isset($metrics95[$row->getValue('uri')])) {
                    $metrics95[$row->getValue('uri')] = [];
                }
                $metrics95[$row->getValue('uri')][$metric] = $row->getValue('95thPer');
                $metrics95[$row->getValue('uri')]['article_id'] = $row->getValue('article_id');
                $metrics95[$row->getValue('uri')]['clang_id'] = $row->getValue('clang_id');
            }
        }

        $sql95 = rex_sql::factory();
        $sql95->setTable(rex::getTable('webvitals_95p'));
        foreach ($metrics95 as $uri => $metrics) {
            $sql95->addRecord(
                function (rex_sql $record) use ($uri, $metrics) {
                    $record->setValue('uri', $uri);
                    $record->setValue('urihash', sha1($uri));
                    foreach ($metrics as $metric => $value) {
                        $record->setValue($metric, $value);
                    }
                }
            );
        }
        $sql95->insertOrUpdate();
    }
}
