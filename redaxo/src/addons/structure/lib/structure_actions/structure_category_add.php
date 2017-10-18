<?php
/**
 * @package redaxo\structure
 */
class rex_structure_category_add extends rex_fragment
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
        if (rex_request('form_category_add', 'int', -1) == $this->edit_id) {
            echo $this->getModal();
        }

        $url_params = array_merge($this->url_params, [
            'form_category_add' => $this->edit_id,
        ]);

        return '<a href="'.$this->context->getUrl($url_params).'" '.rex::getAccesskey(rex_i18n::msg('add_category'), 'add').'><i class="rex-icon rex-icon-add-category"></i></a>';
    }

    /**
     * @return string
     */
    protected function getModal()
    {
        if (!rex::getUser()->getComplexPerm('structure')->hasCategoryPerm($this->edit_id)) {
            return '';
        }

        $clang = rex_clang::exists($this->clang) ? $this->clang : rex_clang::getStartId();
        $data_colspan = 5; // Only for BC reasons

        // EXTENSION POINT
        $cat_form_buttons = rex_extension::registerPoint(new rex_extension_point('CAT_FORM_BUTTONS', '', [
            'id' => $this->edit_id,
            'clang' => $clang,
        ]));

        // EXTENSION POINT
        $cat_form_add = rex_extension::registerPoint(new rex_extension_point('CAT_FORM_ADD', '', [
            'id' => $this->edit_id,
            'clang' => $clang,
            'data_colspan' => ($data_colspan + 1),
        ]));

        return '  
            <div class="modal fade" id="category-add-'.$this->edit_id.'">
                <div class="modal-dialog">
                    <form id="rex-form-category-add-'.$this->edit_id.'" class="modal-content form-horizontal" action="'.$this->context->getUrl($this->url_params).'" method="post" enctype="multipart/form-data" data-pjax-container="#rex-page-main">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span></button>
                            <h3 class="modal-title">'.rex_i18n::msg('header_category').'</h3>
                        </div>
                        <div class="modal-body">
                            <input type="hidden" name="rex-api-call" value="category_add" />
                            <input type="hidden" name="parent-category-id" value="'.$this->edit_id.'" />
                            <dl class="dl-horizontal text-left">
                                <dt><label for="category-name">'.rex_i18n::msg('header_category').'</label></dt>
                                <dd><input id="category-name" class="form-control rex-js-autofocus" type="text" name="category-name" autofocus /></dd>
                            </dl>
                            <dl class="dl-horizontal text-left">
                                <dt><label for="category-position">'.rex_i18n::msg('header_priority').'</label></dt>
                                <dd><input id="category-position" class="form-control" type="text" name="category-position" value="'.($this->pager->getRowCount() + 1).'" /></dd>
                            </dl>
                            '.$cat_form_buttons.'
                            '.$cat_form_add.'
                        </div>
                        <div class="modal-footer">
                            <button class="btn btn-save" type="submit" name="category-add-button" '.rex::getAccesskey(rex_i18n::msg('add_category'), 'save').'>'.rex_i18n::msg('add_category').'</button>
                            <button type="button" class="btn btn-default" data-dismiss="modal">'.rex_i18n::msg('form_abort').'</button>
                        </div>
                    </form>
                </div>
            </div> 
            <script>
                $(document).ready(function() {
                    $("#category-add-'.$this->edit_id.'").modal();
                });
            </script>
        ';
    }
}
