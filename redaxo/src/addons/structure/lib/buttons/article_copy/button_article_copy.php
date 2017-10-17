<?php
/**
 * @author Daniel Weitenauer
 * @copyright (c) 2017 studio ahoi
 */

class rex_button_article_copy extends rex_structure_button
{
    public function get()
    {
        if (!rex::getUser()->hasPerm('copyArticle[]')) {
            return '';
        }

        return '<button type="button" class="btn btn-default" data-toggle="modal" data-target="#article-copy-'.$this->edit_id.'" title="'.rex_i18n::msg('copy_article').'"><i class="rex-icon fa-copy"></i></button>';
    }

    public function getModal()
    {
        if (!rex::getUser()->hasPerm('copyArticle[]')) {
            return '';
        }

        $article = rex_article::get($this->edit_id);
        $article_id = $article->getId();
        $category_id = $article->getCategoryId();

        $move_a = new rex_category_select(false, false, true, !rex::getUser()->getComplexPerm('structure')->hasMountPoints());
        $move_a->setName('category_copy_id_new');
        $move_a->setId('category_copy_id_new');
        $move_a->setSize('1');
        $move_a->setAttribute('class', 'form-control');
        $move_a->setSelected($category_id);

        return '
           <div class="modal fade" id="article-copy-'.$this->edit_id.'">
                <div class="modal-dialog">
                    <form id="rex-form-content-article-copy-'.$this->edit_id.'" class="modal-content form-inline" action="'.$this->context->getUrl().'" method="post" enctype="multipart/form-data" data-pjax-container="#rex-page-main">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span></button>
                            <h3 class="modal-title" id="myModalLabel">'.rex_i18n::msg('content_submitcopyarticle').'</h3>
                        </div>
                        <div class="modal-body">
                            <input type="hidden" name="rex-api-call" value="article_copy" />
                            <input type="hidden" name="article_id" value="'.$article_id.'" />
                            <dl class="dl-horizontal text-left">
                                <dt><label for="category_copy_id_new">'.rex_i18n::msg('copy_article').'</label></dt>
                                <dd>'.$move_a->get().'</dd>
                            </dl>
                        </div>
                        <div class="modal-footer">
                            <button class="btn btn-send" type="submit" data-confirm="'.rex_i18n::msg('content_submitcopyarticle').'?">'.rex_i18n::msg('content_submitcopyarticle').'</button>
                            <button type="button" class="btn btn-default" data-dismiss="modal">'.rex_i18n::msg('form_abort').'</button>
                        </div>
                    </form>
                </div>
            </div> 
        ';
    }
}
