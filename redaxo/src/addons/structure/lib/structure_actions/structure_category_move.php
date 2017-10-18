<?php
/**
 * @package redaxo\structure
 */
class rex_structure_category_move extends rex_fragment
{
    /**
     * @return string
     */
    public function get()
    {
        $article = rex_article::get($this->edit_id);
        $user = rex::getUser();

        if (!$article->isStartArticle() || !$user->hasPerm('moveCategory[]') || !$user->getComplexPerm('structure')->hasCategoryPerm($this->edit_id)) {
            return '';
        }

        // Display form if necessary
        if (rex_request('form_category_move', 'int', -1) == $this->edit_id) {
            echo $this->getModal();
        }

        $url_params = array_merge($this->url_params, [
            'form_category_move' => $this->edit_id,
        ]);

        return '<a href="'.$this->context->getUrl($url_params).'" class="btn btn-default" title="'.rex_i18n::msg('content_submitmovecategory').'"><i class="rex-icon fa-cut"></i></a>';
    }

    /**
     * @return string
     */
    protected function getModal()
    {
        $article = rex_article::get($this->edit_id);
        $user = rex::getUser();

        if (!$article->isStartArticle() || !$user->hasPerm('moveCategory[]') || !$user->getComplexPerm('structure')->hasCategoryPerm($this->edit_id)) {
            return '';
        }

        $category_select = new rex_category_select(false, false, true, !$user->getComplexPerm('structure')->hasMountPoints());
        $category_select->setId('new-category-id');
        $category_select->setName('new-category-id');
        $category_select->setSize('1');
        $category_select->setAttribute('class', 'form-control');
        $category_select->setSelected($this->edit_id);

        return '  
            <div class="modal fade" id="category-move-'.$this->edit_id.'">
                <div class="modal-dialog">
                    <form id="rex-form-category-move-'.$this->edit_id.'" class="modal-content form-horizontal" action="'.$this->context->getUrl().'" method="post" enctype="multipart/form-data" data-pjax-container="#rex-page-main">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span></button>
                            <h3 class="modal-title">'.rex_i18n::msg('content_submitmovecategory').'</h3>
                        </div>
                        <div class="modal-body">
                            <input type="hidden" name="rex-api-call" value="category_move" />
                            <input type="hidden" name="category-id" value="'.$this->edit_id.'" />
                            <dl class="dl-horizontal text-left">
                                <dt><label for="new-category-id">'.rex_i18n::msg('move_category').'</label></dt>
                                <dd>'.$category_select->get().'</dd>
                            </dl>
                        </div>
                        <div class="modal-footer">
                            <button class="btn btn-send" type="submit" data-confirm="'.rex_i18n::msg('content_submitmovecategory').'?">'.rex_i18n::msg('content_submitmovecategory').'</button>
                            <button type="button" class="btn btn-default" data-dismiss="modal">'.rex_i18n::msg('form_abort').'</button>
                        </div>
                    </form>
                </div>
            </div> 
            <script>
                $(document).ready(function() {
                    $("#category-move-'.$this->edit_id.'").modal();
                });
            </script>        
        ';
    }
}
