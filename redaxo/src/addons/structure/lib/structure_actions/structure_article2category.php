<?php
/**
 * @package redaxo\structure
 */
class rex_structure_article2category extends rex_fragment
{
    /**
     * @return string
     */
    public function get()
    {
        // User has no permission or article is already category
        if (rex_article::get($this->edit_id)->isStartArticle() || !rex::getUser()->hasPerm('article2category[]')) {
            return '';
        }

        $url_params = array_merge($this->url_params, [
            'rex-api-call' => 'article2Category',
            'article_id' => $this->edit_id,
        ]);

        return '<a class="btn btn-default" href="'.$this->context->getUrl($url_params).'" data-confirm="'.rex_i18n::msg('content_tocategory').'?" title="'.rex_i18n::msg('content_tocategory').'"><i class="rex-icon rex-icon-category"></i></a>';
    }
}
