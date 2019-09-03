<?php
class rex_structure_action_submit extends rex_structure_action_base
{
    /**
     * @return string
     */
    public function get()
    {
        if (!$this->structure_context->hasCategoryPermission()) {
            return '';
        }

        if ($this->structure_context->getFunction() == 'add_art') {
            $name = 'artadd_function';
            $access_keys = rex::getAccesskey(rex_i18n::msg('article_add'), 'save');
            $text = rex_i18n::msg('article_add');
        } elseif ($this->structure_context->getFunction() == 'edit_art' && $this->sql->getValue('id') == $this->structure_context->getArticleId()) {
            $name = 'artedit_function';
            $access_keys = rex::getAccesskey(rex_i18n::msg('article_save'), 'save');
            $text = rex_i18n::msg('article_save');
        }

        return '
            <td class="rex-table-action" colspan="3">
                '.rex_api_article_add::getHiddenFields().'
                <button class="btn btn-save" type="submit" name="'.$name.'"'.$access_keys.'>'.$text.'</button>
            </td>
        ';
    }
}
