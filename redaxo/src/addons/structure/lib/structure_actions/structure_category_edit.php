<?php
/**
 * @package redaxo\structure
 */
class rex_structure_category_edit extends rex_fragment
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
        if (rex_request('form_category_edit', 'int', -1) == $this->edit_id) {
            echo $this->getModal();
        }

        $url_params = array_merge($this->url_params, [
            'form_category_edit' => $this->edit_id,
        ]);

        return '<a href="'.$this->context->getUrl($url_params).'" class="btn btn-default" title="'.rex_i18n::msg('change').'"><i class="rex-icon rex-icon-edit"></i></a>';
    }

    /**
     * @return string
     */
    protected function getModal()
    {
        if (!rex::getUser()->getComplexPerm('structure')->hasCategoryPerm($this->edit_id)) {
            return '';
        }

        $clang = rex_request('clang', 'int');
        $clang = rex_clang::exists($clang) ? $clang : rex_clang::getStartId();
        $data_colspan = 5; // Only for BC reasons

        // Extension point
        $cat_form_buttons = rex_extension::registerPoint(new rex_extension_point('CAT_FORM_BUTTONS', '', [
           'id' => $this->edit_id,
           'clang' => $clang,
        ]));

        // Extension point
        $cat_form_edit = rex_extension::registerPoint(new rex_extension_point('CAT_FORM_EDIT', '', [
            'id' => $this->edit_id,
            'clang' => $clang,
            'category' => $this->sql,
            'catname' => $this->sql->getValue('catname'),
            'catpriority' => $this->sql->getValue('catpriority'),
            'data_colspan' => ($data_colspan + 1),
        ]));

        return '  
            <div class="modal fade" id="category-edit-'.$this->edit_id.'">
                <div class="modal-dialog">
                    <form id="rex-form-category-move-'.$this->edit_id.'" class="modal-content form-horizontal" action="'.$this->context->getUrl().'" method="post" enctype="multipart/form-data" data-pjax-container="#rex-page-main">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span></button>
                            <h3 class="modal-title">'.rex_i18n::msg('header_category').'</h3>
                        </div>
                        <div class="modal-body">
                            <input type="hidden" name="rex-api-call" value="category_edit" />
                            <input type="hidden" name="category-id" value="'.$this->edit_id.'" />
                            <dl class="dl-horizontal text-left">
                                <dt>'.rex_i18n::msg('header_id').'</dt>
                                <dd>'.$this->edit_id.'</dd>
                            </dl>
                            <dl class="dl-horizontal text-left">
                                <dt><label for="category-name">'.rex_i18n::msg('header_category').'</label></dt>
                                <dd><input class="form-control rex-js-autofocus" type="text" name="category-name" value="'.htmlspecialchars($this->sql->getValue('catname')).'" autofocus /></dd>
                            </dl>
                            <dl class="dl-horizontal text-left">
                                <dt><label for="category-position">'.rex_i18n::msg('header_priority').'</label></dt>
                                <dd><input class="form-control" type="text" name="category-position" value="'.htmlspecialchars($this->sql->getValue('catpriority')).'" /></dd>
                            </dl>
                            '.$cat_form_buttons.'
                            '.$cat_form_edit.'
                        </div>
                        <div class="modal-footer">
                            <button class="btn btn-send" type="submit" name="category-edit-button" '.rex::getAccesskey(rex_i18n::msg('save_category'), 'save').'>'.rex_i18n::msg('save_category').'</button>
                            <button type="button" class="btn btn-default" data-dismiss="modal">'.rex_i18n::msg('form_abort').'</button>
                        </div>
                    </form>
                </div>
            </div> 
            <script>
                $(document).ready(function() {
                    $("#category-edit-'.$this->edit_id.'").modal();
                });
            </script>
        ';
   }
}
