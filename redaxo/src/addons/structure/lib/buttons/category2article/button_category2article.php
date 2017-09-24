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

        // Check if category has children, if it does, its type cannot be changed to article
        $sql = rex_sql::factory();
        #$sql->setDebug(true);
        $sql->setQuery('SELECT pid FROM '.rex::getTable('article').' WHERE parent_id=? LIMIT 1', [$this->edit_id]);

        $button = '<i class="rex-icon rex-icon-article"></i>';

        if ($sql->getRows() != 0) {
            return '';#<span class="btn btn-default text-muted" title="'.rex_i18n::msg('content_nottoarticle').'">'.$button.'</span>';
        }

        // Category type can be changed to article
        $url = $this->context->getUrl([
            'rex-api-call' => 'category2article',
            'article_id' => $this->edit_id,
            'catstart' => rex_request('catstart', 'int'),
        ]);

        return '<a class="btn btn-default" href="'.$url.'" data-confirm="'.rex_i18n::msg('content_toarticle').'?" title="'.rex_i18n::msg('content_toarticle').'">'.$button.'</a>';
    }
}
