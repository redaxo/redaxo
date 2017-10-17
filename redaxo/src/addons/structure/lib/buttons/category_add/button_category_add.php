<?php
/**
 * @author Daniel Weitenauer
 * @copyright (c) 2017 studio ahoi
 */
class rex_button_category_add extends rex_structure_button
{
    public function get()
    {
        if (!rex::getUser()->getComplexPerm('structure')->hasCategoryPerm($this->edit_id)) {
            return '';
        }

        return '<button type="button" class="btn btn-default" data-toggle="modal" data-target="#category-add-'.$this->edit_id.'" '.rex::getAccesskey(rex_i18n::msg('add_category'), 'add').'><i class="rex-icon rex-icon-add-category"></i></button>';
    }

    public function getModal()
    {
        if (!rex::getUser()->getComplexPerm('structure')->hasCategoryPerm($this->edit_id)) {
            return '';
        }

        if (isset($this->pager)) {
            $pager_value = $this->pager->getRowCount() + 1;
        } else {
            $pager_value = 0;
        }

        $clang = rex_request('clang', 'int');
        $clang = rex_clang::exists($clang) ? $clang : rex_clang::getStartId();

        $data_colspan = 5;

        // Extension point
        $cat_form_buttons = rex_extension::registerPoint(new rex_extension_point('CAT_FORM_BUTTONS', '', [
            'id' => $this->edit_id,
            'clang' => $clang,
        ]));

        // Extension point
        $cat_form_add = rex_extension::registerPoint(new rex_extension_point('CAT_FORM_ADD', '', [
            'id' => $this->edit_id,
            'clang' => $clang,
            'data_colspan' => ($data_colspan + 1),
        ]));

        $url = $this->context->getUrl([
            'catstart' => rex_request('catstart', 'int'),
        ]);

        return '  
            <div class="modal fade" id="category-add-'.$this->edit_id.'">
                <div class="modal-dialog">
                    <form id="rex-form-category-add-'.$this->edit_id.'" class="modal-content form-horizontal" action="'.$url.'" method="post" enctype="multipart/form-data" data-pjax-container="#rex-page-main">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span></button>
                            <h3 class="modal-title" id="myModalLabel">'.rex_i18n::msg('header_category').'</h3>
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
                                <dd><input id="category-position" class="form-control" type="text" name="category-position" value="'.$pager_value.'" /></dd>
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
        ';
    }
}
