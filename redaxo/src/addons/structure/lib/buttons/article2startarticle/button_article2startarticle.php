<?php
/**
 * @author Daniel Weitenauer
 * @copyright (c) 2017 studio ahoi
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

        /*if ($article->isStartArticle()) {
            return '<span class="btn text-muted">'.rex_i18n::msg('content_isstartarticle').'</span>';
        }*/

        $url = $this->context->getUrl([
            'rex-api-call' => 'article2startarticle',
            'article_id' => $this->edit_id,
            'catstart' => rex_request('catstart', 'int'),
        ]);

        return '<a class="btn btn-default" href="'.$url.'" data-confirm="'.rex_i18n::msg('content_tostartarticle').'?" title="'.rex_i18n::msg('content_tostartarticle').'"><i class="rex-icon rex-icon-sitestartarticle"></i></a>';
    }
}
