<?php
/**
 * Generate button to change article type to start article
 *
 * @package redaxo\structure
 */
class rex_button_article2Startarticle extends rex_structure_button
{
    public function get()
    {
        $article = rex_article::get($this->edit_id);

        // User has no permission or article is in root
        if ($article->isStartArticle() || !rex::getUser()->hasPerm('article2startarticle[]') || !$article->getParentId()) {
            return '';
        }

        $params = array_merge($this->params, [
            'rex-api-call' => 'article2startarticle',
            'article_id' => $this->edit_id,
        ]);

        return '<a class="btn btn-default" href="'.$this->context->getUrl($params).'" data-confirm="'.rex_i18n::msg('content_tostartarticle').'?" title="'.rex_i18n::msg('content_tostartarticle').'"><i class="rex-icon rex-icon-sitestartarticle"></i></a>';
    }
}
