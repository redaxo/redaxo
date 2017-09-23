<?php
/**
 * @author Daniel Weitenauer
 * @copyright (c) 2017 studio ahoi
 */

class rex_button_category_move extends rex_structure_button
{
    public function get()
    {
        $article = rex_article::get($this->edit_id);
        $category_id = $article->getCategoryId();
        $user = rex::getUser();

        if (!$article->isStartArticle() || !$user->hasPerm('moveCategory[]') || !$user->getComplexPerm('structure')->hasCategoryPerm($category_id)) {
            return '';
        }

        $move_a = new rex_category_select(false, false, true, !$user->getComplexPerm('structure')->hasMountPoints());
        $move_a->setId('category_id_new');
        $move_a->setName('category_id_new');
        $move_a->setSize('1');
        $move_a->setAttribute('class', 'form-control');
        $move_a->setSelected($this->edit_id);

        return '        
            <form id="rex-form-content-metamode" action="'.$this->context->getUrl().'" method="post" enctype="multipart/form-data" data-pjax-container="#rex-page-main">
                <input type="hidden" name="rex-api-call" value="category_move" />
                <input type="hidden" name="category_id" value="'.$category_id.'" />
                <label for="category_id_new">'.rex_i18n::msg('move_category').'</label>
                '.$move_a->get().'
                <button class="btn btn-send" type="submit" data-confirm="'.rex_i18n::msg('content_submitmovecategory').'?">'.rex_i18n::msg('content_submitmovecategory').'</button>
            </form>
        ';
    }
}
