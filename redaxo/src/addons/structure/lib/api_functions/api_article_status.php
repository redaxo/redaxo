<?php
/**
 * @package redaxo\structure
 *
 * @internal
 */
class rex_button_article_status extends rex_structure_button
{
    public function get()
    {
        $article = rex_article::get($this->edit_id);
        $category_id = $article->getCategoryId();
        $user = rex::getUser();

        if ($article->isStartArticle() || !$user->hasPerm('publishArticle[]') || !$user->getComplexPerm('structure')->hasCategoryPerm($category_id)) {
            return '';
        }

        $status_index = (int) $article->isOnline();
        $states = rex_article_service::statusTypes();
        $status = $states[$status_index][0];
        $status_class = $states[$status_index][1];
        $status_icon = $states[$status_index][2];

        $params = array_merge($this->params, [
            'rex-api-call' => 'article_status',
            'article_id' => $this->edit_id,
        ]);

        return '<a class="btn btn-default '.$status_class.'" href="'.$this->context->getUrl($params).'" title="'.$status.'"><i class="rex-icon '.$status_icon.'"></i></a>';
    }
}
