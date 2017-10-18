<?php
/**
 * @package redaxo\structure
 */
class rex_structure_article_move extends rex_fragment
{
    /**
     * @return string
     */
    public function get()
    {
        $article = rex_article::get($this->edit_id);
        $article_id = $article->getId();
        $category_id = $article->getCategoryId();

        if ($article->isStartArticle() || !rex::getUser()->hasPerm('moveArticle[]') || $category_id == $article_id) {
            return '';
        }

        // Display form if necessary
        if (rex_request('form_article_move', 'int', -1) == $this->edit_id) {
            echo $this->getModal();
        }

        $url_params = array_merge($this->url_params, [
            'form_article_move' => $this->edit_id,
        ]);

        return '<a href="'.$this->context->getUrl($url_params).'" class="btn btn-default" title="'.rex_i18n::msg('content_submitmovearticle').'"><i class="rex-icon fa-cut"></i></a>';
    }

    /**
     * @return string
     */
    protected function getModal()
    {
        $article = rex_article::get($this->edit_id);
        $article_id = $article->getId();
        $category_id = $article->getCategoryId();
        $user = rex::getUser();

        if ($article->isStartArticle() || !$user->hasPerm('moveArticle[]') || $category_id == $article_id) {
            return '';
        }

        $category_select = new rex_category_select(false, false, true, !$user->getComplexPerm('structure')->hasMountPoints());
        $category_select->setId('new-category-id');
        $category_select->setName('new-category-id');
        $category_select->setSize('1');
        $category_select->setAttribute('class', 'form-control');
        $category_select->setSelected($category_id);

        return '  
            <div class="modal fade" id="article-move-'.$this->edit_id.'">
                <div class="modal-dialog">
                    <form id="rex-form-content-article-move-'.$this->edit_id.'" class="modal-content form-inline" action="'.$this->context->getUrl().'" method="post" enctype="multipart/form-data" data-pjax-container="#rex-page-main">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span></button>
                            <h3 class="modal-title">'.rex_i18n::msg('content_submitmovearticle').'</h3>
                        </div>
                        <div class="modal-body">
                            <input type="hidden" name="rex-api-call" value="article_move" />
                            <input type="hidden" name="article_id" value="'.$article_id.'" />
                            <dl class="dl-horizontal text-left">
                                <dt><label for="new-category-id">'.rex_i18n::msg('move_article').'</label></dt>
                                <dd>'.$category_select->get().'</dd>
                            </dl>
                        </div>
                        <div class="modal-footer">
                            <button class="btn btn-send" type="submit" data-confirm="'.rex_i18n::msg('content_submitmovearticle').'?">'.rex_i18n::msg('content_submitmovearticle').'</button>
                            <button type="button" class="btn btn-default" data-dismiss="modal">'.rex_i18n::msg('form_abort').'</button>
                        </div>
                    </form>
                </div>
            </div> 
            <script>
                $(document).ready(function() {
                    $("#article-move-'.$this->edit_id.'").modal();
                });
            </script>        
        ';
    }
}
