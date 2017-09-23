<?php
/**
 * @package redaxo\structure
 */
class rex_button_category_delete extends rex_structure_button
{
    public function get()
    {
        $article = rex_article::get($this->edit_id);
        $category_id = $article->getCategoryId();
        $user = rex::getUser();

        #$button = '<i class="rex-icon rex-icon-delete"></i> '.rex_i18n::msg('delete');
        $button = '<i class="rex-icon rex-icon-delete"></i>';

        if (!$user->getComplexPerm('structure')->hasCategoryPerm($category_id)) {
            return '<span class="btn btn-default text-muted">'.$button.'</span>';
        }

        $url =  $this->context->getUrl([
            'rex-api-call' => 'category_delete',
            'category-id' => $this->edit_id,
            'catstart' => rex_request('catstart', 'int'),
        ]);

        return '<a class="btn btn-default" href="'.$url.'" data-confirm="'.rex_i18n::msg('delete').'?" title="'.rex_i18n::msg('delete').'">'.$button.'</a>';
    }
}
