<?php
/**
 * @package redaxo\structure
 */
class rex_button_article_delete extends rex_structure_button
{
    public function get()
    {
        $article = rex_article::get($this->edit_id);

        if ($article->isStartArticle()) {
            return '<span class="text-muted"><i class="rex-icon rex-icon-delete"></i> '.rex_i18n::msg('delete').'</span>';
        }
        $url = $this->context->getUrl([
            'rex-api-call' => 'article_delete',
            'article_id' => $this->edit_id,
            'artstart' => rex_request('artstart', 'int'),
        ]);

        return '<a href="'.$url.'" data-confirm="'.rex_i18n::msg('delete').' ?"><i class="rex-icon rex-icon-delete"></i> '.rex_i18n::msg('delete').'</a>';
    }
}
