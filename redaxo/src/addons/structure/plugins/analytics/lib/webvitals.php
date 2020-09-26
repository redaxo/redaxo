<?php

final class rex_analytics_webvitals {
    /**
     * @var rex_analytics_metric
     */
    public $lcp;
    /**
     * @var rex_analytics_metric
     */
    public $fid;
    /**
     * @var rex_analytics_metric
     */
    public $cls;

    /**
     * @return self|null
     */
    static public function forArticle(int $article_id, int $clang_id) {
        $sql95 = rex_sql::factory();
        $sql95->setQuery('SELECT cls, fid, lcp FROM '.rex::getTable('webvitals_95p').' WHERE article_id = :articleId AND clang_id = :clangId', ['articleId' => $article_id, 'clangId' => $clang_id]);

        if (1 === $sql95->getRows()) {
            $lcp = rex_analytics_metric::forValue($sql95->getValue('lcp'), rex_analytics_metric::TYPE_LCP);
            $fid = rex_analytics_metric::forValue($sql95->getValue('fid'), rex_analytics_metric::TYPE_FID);
            $cls = rex_analytics_metric::forValue($sql95->getValue('cls'), rex_analytics_metric::TYPE_CLS);

            $vitals = new self();
            $vitals->lcp = $lcp;
            $vitals->fid = $fid;
            $vitals->cls = $cls;
            return $vitals;
        }

        return null;
    }
}
