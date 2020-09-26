<?php

final class rex_analytics_webvitals_storage {

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
        foreach (['cls', 'fid', 'lcp'] as $metric) {
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

        if ($metrics95) {
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

    public function storeData(string $uri, stdClass $data, rex_article $article) {
        $sql = rex_sql::factory();
        $sql->setTable(rex::getTable('webvitals'));
        $sql->addRecord(function (rex_sql $record) use ($uri, $data, $article) {
            $record->setValue('uri', $uri);
            $record->setValue('article_id', $article->getId());
            $record->setValue('clang_id', $article->getClangId());

            switch($data->name) {
                case 'CLS': {
                    $record->setValue('cls', $data->value * 1000);
                    break;
                }
                case 'FID': {
                    $record->setValue('fid', $data->value);
                    break;
                }
                case 'LCP': {
                    $record->setValue('lcp', $data->value);
                    break;
                }
            }
        });
        $sql->insert();
    }
}
