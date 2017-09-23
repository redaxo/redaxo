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

        return '<button type="button" class="btn btn-default" data-toggle="modal" data-target="#article-move-'.$this->edit_id.'" title="'.rex_i18n::msg('content_submitmovearticle').'"><i class="rex-icon fa-cut"></i></button>';
    }

    public function getModal()
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
            <div class="modal fade" id="article-move-'.$this->edit_id.'">
                <div class="modal-dialog">
                    <form id="rex-form-content-article-move-'.$this->edit_id.'" class="modal-content form-inline" action="'.$this->context->getUrl().'" method="post" enctype="multipart/form-data" data-pjax-container="#rex-page-main">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span></button>
                            <h3 class="modal-title" id="myModalLabel">'.rex_i18n::msg('content_submitmovearticle').'</h3>
                        </div>
                        <div class="modal-body">
                            <input type="hidden" name="rex-api-call" value="article_move" />
                            <input type="hidden" name="article_id" value="'.$article_id.'" />
                            <dl class="dl-horizontal text-left">
                                <dt><label for="category_id_new">'.rex_i18n::msg('move_article').'</label></dt>
                                <dd>'.$move_a->get().'</dd>
                            </dl>
                        </div>
                        <div class="modal-footer">
                            <button class="btn btn-send" type="submit" data-confirm="'.rex_i18n::msg('content_submitmovearticle').'?">'.rex_i18n::msg('content_submitmovearticle').'</button>
                            <button type="button" class="btn btn-default" data-dismiss="modal">'.rex_i18n::msg('form_abort').'</button>
                        </div>
                    </form>
                </div>
            </div> 
        ';
    }
}
