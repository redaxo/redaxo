<?php
/**
 * Generate button to change category type to article
 *
 * @package redaxo\structure
 */
class rex_button_category2Article extends rex_structure_button
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

        if (!isset($this->edit_id)) {
            throw new rex_exception('No article id was set!');
        }

        // Check if category has children, if it does, its type cannot be changed to article
        $sql = rex_sql::factory();
        #$sql->setDebug(true);
        $sql->setQuery('SELECT pid FROM '.rex::getTable('article').' WHERE parent_id=? LIMIT 1', [$this->edit_id]);

        if ($sql->getRows() != 0) {
            return '<span class="text-muted">'.rex_i18n::msg('content_nottoarticle').'</span>';
        }

        // Category type can be changed to article
        $url = $this->context->getUrl([
            'rex-api-call' => 'category2article',
            'article_id' => $this->edit_id,
            'catstart' => rex_request('catstart', 'int'),
        ]);

        return '<a class="btn btn-default" href="'.$url.'" data-confirm="'.rex_i18n::msg('content_toarticle').'?">'.rex_i18n::msg('content_toarticle').'</a>';
    }
}
