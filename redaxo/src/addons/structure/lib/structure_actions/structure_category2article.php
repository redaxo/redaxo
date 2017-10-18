<?php
/**
 * @package redaxo\structure
 */
class rex_structure_category2Article extends rex_fragment
{
    /**
     * @return string
     * @throws rex_exception
     */
    public function get()
    {
        $article = rex_article::get($this->edit_id);
        $user = rex::getUser();

        if (!$article->isStartArticle() || !$user->hasPerm('article2category[]') || !$user->getComplexPerm('structure')->hasCategoryPerm($article->getCategoryId())) {
            return '';
        }

        // Check if category has children, if it does, its type cannot be changed
        $sql = rex_sql::factory();
        $sql->setQuery('SELECT pid FROM '.rex::getTable('article').' WHERE parent_id=? LIMIT 1', [$this->edit_id]);

        if ($sql->getRows() != 0) {
            return '';
        }

        $url_params = array_merge($this->url_params, [
            'rex-api-call' => 'category2article',
            'article_id' => $this->edit_id,
        ]);

        return '<a class="btn btn-default" href="'. $this->context->getUrl($url_params).'" data-confirm="'.rex_i18n::msg('content_toarticle').'?" title="'.rex_i18n::msg('content_toarticle').'"><i class="rex-icon rex-icon-article"></i></a>';
    }
}
