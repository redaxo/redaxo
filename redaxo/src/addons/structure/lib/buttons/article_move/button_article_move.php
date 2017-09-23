<?php
/**
 * @author Daniel Weitenauer
 * @copyright (c) 2017 studio ahoi
 */

class rex_button_article_move extends rex_structure_button
{
    public function get()
    {
        $article = rex_article::get($this->edit_id);
        $article_id = $article->getId();
        $category_id = $article->getCategoryId();
        $user = rex::getUser();

        if ($article->isStartArticle() || !$user->hasPerm('moveArticle[]') || $category_id == $article_id) {
            return '';
        }

        $move_a = new rex_category_select(false, false, true, !$user->getComplexPerm('structure')->hasMountPoints());
        $move_a->setId('category_id_new');
        $move_a->setName('category_id_new');
        $move_a->setSize('1');
        $move_a->setAttribute('class', 'form-control');
        $move_a->setSelected($category_id);

        return '        
            <form id="rex-form-content-metamode" action="'.$this->context->getUrl().'" method="post" enctype="multipart/form-data" data-pjax-container="#rex-page-main">
                <input type="hidden" name="rex-api-call" value="article_move" />
                <input type="hidden" name="article_id" value="'.$article_id.'" />
                <label for="category_id_new">'.rex_i18n::msg('move_article').'</label>
                '.$move_a->get().'
                <button class="btn btn-send" type="submit" data-confirm="'.rex_i18n::msg('content_submitmovearticle').'?">'.rex_i18n::msg('content_submitmovearticle').'</button>
            </form>
        ';
    }
}
