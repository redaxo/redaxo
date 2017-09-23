<?php
/**
 * @package redaxo\structure
 */
class rex_button_category_edit extends rex_structure_button
{
    public function get()
    {
        $article = rex_article::get($this->edit_id);
        $category_id = $article->getCategoryId();
        $user = rex::getUser();

        #$button = '<i class="rex-icon rex-icon-edit"></i> '.rex_i18n::msg('change');
        $button = '<i class="rex-icon rex-icon-edit"></i>';

        if (!$user->getComplexPerm('structure')->hasCategoryPerm($category_id)) {
            return '<span class="btn text-muted">'.$button.'</span>';
        }

        $url =  $this->context->getUrl([
            'edit_id' => $this->edit_id,
            'function' => 'edit_cat',
            'catstart' => rex_request('catstart', 'int')
        ]);

        return '<a class="btn btn-default" href="'.$url.'" title="'.rex_i18n::msg('change').'">'.$button.'</a>';
   }
}
