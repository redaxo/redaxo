<?php
/**
 * @package redaxo\structure
 */
class rex_structure_article_add extends rex_fragment
{
    /**
     * @return string
     */
    public function get()
    {
        if (!rex::getUser()->getComplexPerm('structure')->hasCategoryPerm($this->edit_id)) {
            return '';
        }

        // Display form if necessary
        if (rex_request('form_article_add', 'int', -1) == $this->edit_id) {
            echo $this->getModal();
        }

        $url_params = array_merge($this->url_params, [
            'form_article_add' => $this->edit_id,
        ]);

        return '<a href="'.$this->context->getUrl($url_params).'" '.rex::getAccesskey(rex_i18n::msg('article_add'), 'add_2').'><i class="rex-icon rex-icon-add-article"></i></a>';
    }

    /**
     * @return string
     */
    protected function getModal()
    {
        if (!rex::getUser()->getComplexPerm('structure')->hasCategoryPerm($this->edit_id)) {
            return '';
        }

        $template_select = '';
        if (rex_addon::get('structure')->getPlugin('content')->isAvailable()) {
            $select = new rex_template_select();
            $select->setName('template_id');
            $select->setSize(1);
            $select->setStyle('class="form-control"');
            $select->setSelected();

            $template_select = '
                <dl class="dl-horizontal text-left">
                    <dt><label for="article-name">'.rex_i18n::msg('header_template').'</label></dt>
                    <dd>'.$select->get().'</dd>
                </dl>
            ';
        }

        return '  
            <div class="modal fade" id="article-add-'.$this->edit_id.'">
                <div class="modal-dialog">
                    <form id="rex-form-article-add-'.$this->edit_id.'" class="modal-content form-horizontal" action="'.$this->context->getUrl($this->url_params).'" method="post" enctype="multipart/form-data" data-pjax-container="#rex-page-main">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span></button>
                            <h3 class="modal-title">'.rex_i18n::msg('article_add').'</h3>
                        </div>
                        <div class="modal-body">
                            <input type="hidden" name="rex-api-call" value="article_add" />
                            <dl class="dl-horizontal text-left">
                                <dt><label for="article-name">'.rex_i18n::msg('header_article_name').'</label></dt>
                                <dd><input class="form-control" type="text" name="article-name" autofocus /></dd>
                            </dl>
                            '.$template_select.'
                            <dl class="dl-horizontal text-left">
                                <dt>'.rex_i18n::msg('header_date').'</dt>
                                <dd>'.rex_formatter::strftime(time(), 'date').'</dd>
                            </dl>
                            <dl class="dl-horizontal text-left">
                                <dt><label for="article-position">'.rex_i18n::msg('header_priority').'</label></dt>
                                <dd><input id="article-position" class="form-control" type="text" name="article-position" value="'.($this->pager->getRowCount() + 1).'" /></dd>
                            </dl>
                        </div>
                        <div class="modal-footer">
                            <button class="btn btn-save" type="submit" name="artadd_function" '.rex::getAccesskey(rex_i18n::msg('article_add'), 'save').'>'.rex_i18n::msg('article_add').'</button>
                            <button type="button" class="btn btn-default" data-dismiss="modal">'.rex_i18n::msg('form_abort').'</button>
                        </div>
                    </form>
                </div>
            </div> 
            <script>
                $(document).ready(function() {
                    $("#article-add-'.$this->edit_id.'").modal();
                });
            </script>        
        ';
    }
}
