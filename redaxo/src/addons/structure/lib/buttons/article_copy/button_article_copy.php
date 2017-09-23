<?php
/**
 * @author Daniel Weitenauer
 * @copyright (c) 2017 studio ahoi
 */

class rex_button_article_copy extends rex_structure_button
{
    public function get()
    {
        $article = rex_article::get($this->edit_id);
        $article_id = $article->getId();
        $category_id = $article->getCategoryId();
        $user = rex::getUser();

        if (!$user->hasPerm('copyArticle[]')) {
            return '';
        }

        $move_a = new rex_category_select(false, false, true, !rex::getUser()->getComplexPerm('structure')->hasMountPoints());
        $move_a->setName('category_copy_id_new');
        $move_a->setId('category_copy_id_new');
        $move_a->setSize('1');
        $move_a->setAttribute('class', 'form-control');
        $move_a->setSelected($category_id);

        return '        
            <form id="rex-form-content-metamode" action="'.$this->context->getUrl().'" method="post" enctype="multipart/form-data" data-pjax-container="#rex-page-main">
                <input type="hidden" name="rex-api-call" value="article_copy" />
                <input type="hidden" name="article_id" value="'.$article_id.'" />
                <label for="category_copy_id_new">'.rex_i18n::msg('copy_article').'</label>
                '.$move_a->get().'
                <button class="btn btn-send" type="submit" data-confirm="'.rex_i18n::msg('content_submitcopyarticle').'?">'.rex_i18n::msg('content_submitcopyarticle').'</button>
            </form>
        ';
    }
}
