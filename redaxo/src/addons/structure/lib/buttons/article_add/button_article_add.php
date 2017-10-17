<?php
/**
 * @package redaxo\structure
 */
class rex_button_article_add extends rex_structure_button
{
    public function get()
    {
        if (!rex::getUser()->getComplexPerm('structure')->hasCategoryPerm($this->edit_id)) {
            return '';
        }

        return '<button type="button" class="btn btn-default" data-toggle="modal" data-target="#article-add-'.$this->edit_id.'" '.rex::getAccesskey(rex_i18n::msg('article_add'), 'add_2').'><i class="rex-icon rex-icon-add-article"></i></button>';
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

        $template_select = '';
        if (rex_addon::get('structure')->getPlugin('content')->isAvailable()) {
            $template_select = '
                <dl class="dl-horizontal text-left">
                    <dt><label for="article-name">'.rex_i18n::msg('header_template').'</label></dt>
                    <dd>'.$this->getTemplateSelect($this->edit_id, $clang).'</dd>
                </dl>
            ';
        }

        $url = $this->context->getUrl([
            'artstart' => rex_request('artstart', 'int'),
        ]);

        return '  
            <div class="modal fade" id="article-add-'.$this->edit_id.'">
                <div class="modal-dialog">
                    <form id="rex-form-article-add-'.$this->edit_id.'" class="modal-content form-horizontal" action="'.$url.'" method="post" enctype="multipart/form-data" data-pjax-container="#rex-page-main">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span></button>
                            <h3 class="modal-title" id="myModalLabel">'.rex_i18n::msg('header_article_name').'</h3>
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
                                <dd><input id="article-position" class="form-control" type="text" name="article-position" value="'.$pager_value.'" /></dd>
                            </dl>
                        </div>
                        <div class="modal-footer">
                            <button class="btn btn-save" type="submit" name="artadd_function" '.rex::getAccesskey(rex_i18n::msg('article_add'), 'save').'>'.rex_i18n::msg('article_add').'</button>
                            <button type="button" class="btn btn-default" data-dismiss="modal">'.rex_i18n::msg('form_abort').'</button>
                        </div>
                    </form>
                </div>
            </div> 
        ';
    }

    protected function getTemplateSelect($category_id, $clang)
    {
        $template_select = new rex_select();
        $template_select->setName('template_id');
        $template_select->setSize(1);
        $template_select->setStyle('class="form-control"');

        $templates = rex_template::getTemplatesForCategory($category_id);
        if (count($templates) > 0) {
            foreach ($templates as $t_id => $t_name) {
                $template_select->addOption(rex_i18n::translate($t_name, false), $t_id);
                $TEMPLATE_NAME[$t_id] = rex_i18n::translate($t_name);
            }
        } else {
            $template_select->addOption(rex_i18n::msg('option_no_template'), '0');
        }
        $TEMPLATE_NAME[0] = rex_i18n::msg('template_default_name');

        $selectedTemplate = 0;
        if ($category_id) {
            // template_id vom Startartikel erben
            $sql2 = rex_sql::factory();
            $sql2->setQuery('SELECT template_id FROM '.rex::getTable('article').' WHERE id='.$category_id.' AND clang_id='.$clang.' AND startarticle = 1');
            if ($sql2->getRows() == 1) {
                $selectedTemplate = $sql2->getValue('template_id');
            }
        }
        if (!$selectedTemplate || !isset($TEMPLATE_NAME[$selectedTemplate])) {
            $selectedTemplate = rex_template::getDefaultId();
        }
        if ($selectedTemplate && isset($TEMPLATE_NAME[$selectedTemplate])) {
            $template_select->setSelected($selectedTemplate);
        }

        return $template_select->get();
    }
}
