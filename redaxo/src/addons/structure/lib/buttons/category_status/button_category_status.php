<?php
/**
 * @package redaxo\structure
 */
class rex_button_category_status extends rex_structure_button
{
    public function get()
    {
        $article = rex_article::get($this->edit_id);
        $status_index = (int) $article->isOnline();
        $category_id = $article->getCategoryId();
        $user = rex::getUser();

        $states = rex_category_service::statusTypes();
        $status = $states[$status_index][0];
        $status_class = $states[$status_index][1];
        $status_icon = $states[$status_index][2];

        #$button = '<i class="rex-icon '.$status_icon.'"></i> '.$status;
        $button = '<i class="rex-icon '.$status_icon.'"></i>';

        if (!$user->hasPerm('publishCategory[]') || !$user->getComplexPerm('structure')->hasCategoryPerm($category_id)) {
            return '<span class="btn btn-default '.$status_class.' text-muted">'.$button.'</span>';
        }

        $url = $this->context->getUrl([
            'rex-api-call' => 'category_status',
            'category-id' => $this->edit_id,
            'catstart' => rex_request('catstart', 'int'),
        ]);

        return '<a class="btn btn-default '.$status_class.'" href="'.$url.'" title="'.$status.'">'.$button.'</a>';
    }
}
