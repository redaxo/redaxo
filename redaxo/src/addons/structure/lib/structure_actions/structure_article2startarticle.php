<?php
/**
 * @package redaxo\structure
 */
class rex_structure_article2Startarticle extends rex_fragment
{
    /**
     * @return string
     */
    public function get()
    {
        $article = rex_article::get($this->edit_id);

        // User has no permission or article is in root
        if ($article->isStartArticle() || !rex::getUser()->hasPerm('article2startarticle[]') || !$article->getParentId()) {
            return '';
        }

        $url_params = array_merge($this->url_params, [
            'rex-api-call' => 'article2startarticle',
            'category_id' => $this->edit_id, // As the active category id also changes, set new target
            'article_id' => $this->edit_id,
        ]);

        return '<a class="btn btn-default" href="'.$this->context->getUrl($url_params).'" data-confirm="'.rex_i18n::msg('content_tostartarticle').'?" title="'.rex_i18n::msg('content_tostartarticle').'"><i class="rex-icon rex-icon-sitestartarticle"></i></a>';
    }
}
