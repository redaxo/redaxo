<?php
/**
 * @package redaxo\structure
 */
class rex_button_article_delete extends rex_structure_button
{
    public function get()
    {
        $article = rex_article::get($this->edit_id);

        #$button = '<i class="rex-icon rex-icon-delete"></i> '.rex_i18n::msg('delete');
        $button = '<i class="rex-icon rex-icon-delete"></i>';

        if (!$article->isStartArticle()) {
            return '';#<span class="btn text-muted">'.$button.'</span>';
        }

        $url = $this->context->getUrl([
            'rex-api-call' => 'article_delete',
            'article_id' => $this->edit_id,
            'artstart' => rex_request('artstart', 'int'),
        ]);

        return '<a class="btn btn-default" href="'.$url.'" data-confirm="'.rex_i18n::msg('delete').'?" title="'.rex_i18n::msg('delete').'">'.$button.'</a>';
    }
}
