<?php
/**
 * @package redaxo\structure
 */
class rex_structure_article_copy extends rex_fragment
{
    /**
     * @return string
     */
    public function get()
    {
        if (!rex::getUser()->hasPerm('copyArticle[]')) {
            return '';
        }

        // Display form if necessary
        if (rex_request('form_article_copy', 'int', -1) == $this->edit_id) {
            echo $this->getModal();
        }

        $url_params = array_merge($this->url_params, [
            'form_article_copy' => $this->edit_id,
        ]);

        return '<a href="'.$this->context->getUrl($url_params).'" class="btn btn-default" title="'.rex_i18n::msg('copy_article').'"><i class="rex-icon fa-copy"></i></a>';
    }

    /**
     * @return string
     */
    protected function getModal()
    {
        if (!rex::getUser()->hasPerm('copyArticle[]')) {
            return '';
        }

        $article = rex_article::get($this->edit_id);
        $article_id = $article->getId();
        $category_id = $article->getCategoryId();

        $category_select = new rex_category_select(false, false, true, !rex::getUser()->getComplexPerm('structure')->hasMountPoints());
        $category_select->setName('category_copy_id_new');
        $category_select->setId('category_copy_id_new');
        $category_select->setSize('1');
        $category_select->setAttribute('class', 'form-control');
        $category_select->setSelected($category_id);

        return '
           <div class="modal fade" id="article-copy-'.$this->edit_id.'">
                <div class="modal-dialog">
                    <form id="rex-form-content-article-copy-'.$this->edit_id.'" class="modal-content form-inline" action="'.$this->context->getUrl().'" method="post" enctype="multipart/form-data" data-pjax-container="#rex-page-main">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span></button>
                            <h3 class="modal-title">'.rex_i18n::msg('content_submitcopyarticle').'</h3>
                        </div>
                        <div class="modal-body">
                            <input type="hidden" name="rex-api-call" value="article_copy" />
                            <input type="hidden" name="article_id" value="'.$article_id.'" />
                            <dl class="dl-horizontal text-left">
                                <dt><label for="category_copy_id_new">'.rex_i18n::msg('copy_article').'</label></dt>
                                <dd>'.$category_select->get().'</dd>
                            </dl>
                        </div>
                        <div class="modal-footer">
                            <button class="btn btn-send" type="submit" data-confirm="'.rex_i18n::msg('content_submitcopyarticle').'?">'.rex_i18n::msg('content_submitcopyarticle').'</button>
                            <button type="button" class="btn btn-default" data-dismiss="modal">'.rex_i18n::msg('form_abort').'</button>
                        </div>
                    </form>
                </div>
            </div> 
            <script>
                $(document).ready(function() {
                    $("#article-copy-'.$this->edit_id.'").modal();
                });
            </script>        
        ';
    }
}
