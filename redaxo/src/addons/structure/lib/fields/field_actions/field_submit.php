<?php
class rex_structure_field_submit extends rex_structure_field_base
{
    /**
     * @return string
     */
    public function get()
    {
        switch ($this->type) {
            case rex_structure_field_group::HEADER:
                return $this->getHeader();
                break;

            case rex_structure_field_group::BODY:
            default:
                return $this->getBody();

        }
    }

    /**
     * @return string
     */
    protected function getBody()
    {
        if (!$this->context->hasCategoryPermission()) {
            return '';
        }

        $button = '<button class="btn btn-save" type="submit" name="%s" %s>%s</button>';

        switch ($this->context->getFunction()) {
            case 'add_art':
                return
                    rex_api_article_add::getHiddenFields().
                    sprintf($button, 'artadd_function', rex::getAccesskey(rex_i18n::msg('article_add'), 'save'), rex_i18n::msg('article_add'));
                break;

            case 'edit_art':
                if ($this->sql->getValue('id') == $this->context->getArticleId()) {
                    return
                        rex_api_article_edit::getHiddenFields().
                        sprintf($button, 'artedit_function', rex::getAccesskey(rex_i18n::msg('article_save'), 'save'), rex_i18n::msg('article_save'));
                }
                break;
        }

        return '';
    }

    /**
     * @return string
     */
    protected function getHeader()
    {
        return rex_i18n::msg('header_status');
    }
}
