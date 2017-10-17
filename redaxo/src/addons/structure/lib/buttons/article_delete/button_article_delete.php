<?php
/**
 * Button to delete article
 *
 * @package redaxo\structure
 */
class rex_button_article_delete extends rex_structure_button
{
    public function get()
    {
        $article = rex_article::get($this->edit_id);

        if (!$article->isStartArticle()) {
            return '';
        }

        $params = array_merge($this->params, [
            'rex-api-call' => 'article_delete',
            'article_id' => $this->edit_id,
        ]);

        return '<a class="btn btn-default" href="'.$this->context->getUrl($params).'" data-confirm="'.rex_i18n::msg('delete').'?" title="'.rex_i18n::msg('delete').'"><i class="rex-icon rex-icon-delete"></i></a>';
    }
}
