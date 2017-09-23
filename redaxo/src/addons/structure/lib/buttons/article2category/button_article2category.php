<?php
/**
 * Generate button to change article type to category
 *
 * @package redaxo\structure
 */
class rex_button_article2category extends rex_structure_button
{
    public function get()
    {
        $article = rex_article::get($this->edit_id);

        // User has no permission or article is already category
        if ($article->isStartArticle() || !rex::getUser()->hasPerm('article2category[]')) {
            return '';
        }

        $url = $this->context->getUrl([
            'rex-api-call' => 'article2Category',
            'article_id' => $this->edit_id,
            'catstart' => rex_request('catstart', 'int'),
        ]);

        return '<a class="btn btn-default" href="'.$url.'" data-confirm="'.rex_i18n::msg('content_tocategory').'?" title="'.rex_i18n::msg('content_tocategory').'"><i class="rex-icon rex-icon-category"></i></a>';
    }
}
