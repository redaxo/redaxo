<?php
/**
 * @author Daniel Weitenauer
 * @copyright (c) 2017 studio ahoi
 */

class rex_button_content_copy extends rex_structure_button
{

    public function get()
    {
        return '<button type="button" class="btn btn-default" data-toggle="modal" data-target="#content-copy">'.rex_i18n::msg('content_submitcopycontent').'</button>';
    }

    public function getModal()
    {
        $user = rex::getUser();
        $clang_perm = $user->getComplexPerm('clang')->getClangs();

        if (!$user->hasPerm('copyContent[]') || $clang_perm <= 1) {
            return '';
        }

        $slice_revision = 0;

        $lang_a = new rex_select();
        $lang_a->setId('clang_a');
        $lang_a->setName('clang_a');
        $lang_a->setSize('1');
        $lang_a->setAttribute('class', 'form-control');
        foreach ($clang_perm as $key) {
            $val = rex_i18n::translate(rex_clang::get($key)->getName());
            $lang_a->addOption($val, $key);
        }
        $lang_a->setSelected(rex_request('clang_a', 'int', null));

        $lang_b = new rex_select();
        $lang_b->setId('clang_b');
        $lang_b->setName('clang_b');
        $lang_b->setSize('1');
        $lang_b->setAttribute('class', 'form-control');
        foreach ($clang_perm as $key) {
            $val = rex_i18n::translate(rex_clang::get($key)->getName());
            $lang_b->addOption($val, $key);
        }
        $lang_b->setSelected(rex_request('clang_b', 'int', null));

        return '  
            <div class="modal fade" id="content-copy">
                <div class="modal-dialog">
                    <form id="rex-form-content-content-copy" class="modal-content form-horizontal" action="'.$this->context->getUrl().'" method="post" enctype="multipart/form-data" data-pjax-container="#rex-page-main">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span></button>
                            <h3 class="modal-title" id="myModalLabel">'.rex_i18n::msg('content_submitcopycontent').'</h3>
                        </div>
                        <div class="modal-body">
                            <input type="hidden" name="rex-api-call" value="content_copy" />
                            <input type="hidden" name="article_id" value="'.$this->edit_id.'" />
                            <input type="hidden" name="slice_revision" value="'.$slice_revision.'" />
                            <dl class="dl-horizontal text-left">
                                <dt><label for="clang_a">'.rex_i18n::msg('content_contentoflang').'</label></dt>
                                <dd>'.$lang_a->get().'</dd>
                            </dl>
                            <dl class="dl-horizontal text-left">
                                    <dt><label for="clang_b">'.rex_i18n::msg('content_to').'</label></dt>
                                    <dd>'.$lang_b->get().'</dd>
                                </dl>
                        </div>
                        <div class="modal-footer">
                            <button class="btn btn-send" type="submit" data-confirm="'.rex_i18n::msg('content_submitcopycontent').'?">'.rex_i18n::msg('content_submitcopycontent').'</button>
                            <button type="button" class="btn btn-default" data-dismiss="modal">'.rex_i18n::msg('form_abort').'</button>
                        </div>
                    </form>
                </div>
            </div> 
        ';
    }
}
